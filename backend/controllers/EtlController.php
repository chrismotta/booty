<?php

namespace backend\controllers;

//require_once '../../vendor/ip2location/ip2location-php/IP2Location.php';
//use Elasticsearch\ClientBuilder;
use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\DeviceParserAbstract;
use DeviceDetector\Parser\Client\ClientParserAbstract;
//use IP2Location;
use Predis;

class EtlController extends \yii\web\Controller
{
    CONST ALERT_FROM = 'Nigma<no-reply@tmlbox.co>';
    CONST ALERT_TO   = 'daniel@themedialab.co,chris@themedialab.co';

	private $_redis;
	private $_objectLimit;
	private $_timestamp;
	private $_limit;
    private $_placementSql;
    private $_showsql;
    private $_sqltest;
    private $_alertSubject;
    private $_error;
    private $_noalerts;
    private $_db;
    private $_test;

    private $_count;


	public function __construct ( $id, $module, $config = [] )
	{
		parent::__construct( $id, $module, $config );

		// enable elastic search client
		//$this->_elasticSearch = ClientBuilder::create()->setHosts(["localhost:9200"])->setSelector('\Elasticsearch\ConnectionPool\Selectors\StickyRoundRobinSelector')->build();

    	$this->_redis 	 	  	= new \Predis\Client( \Yii::$app->params['predisConString'] );

        $this->_objectLimit = isset( $_GET['objectlimit'] ) ? $_GET['objectlimit'] : 50000;

        if ( !preg_match( '/^[0-9]+$/',$this->_objectLimit) || (int)$this->_objectLimit<1 )
        {
            die('invalid object limit');
        }

        $this->_limit = isset( $_GET['limit'] ) ? $_GET['limit'] : false;

        if ( $this->_limit && !preg_match( '/^[0-9]+$/',$this->_limit ) )
        {
            die('invalid limit');
        }
      
        $this->_showsql         = isset( $_GET['showsql'] ) && $_GET['showsql'] ? true : false;
        $this->_noalerts        = isset( $_GET['noalerts'] ) && $_GET['noalerts'] ? true : false;
        $this->_sqltest         = isset( $_GET['sqltest'] ) && $_GET['sqltest'] ? true : false;

        $this->_timestamp       = time();

        $this->_db              = false;

        $this->_alertSubject    = 'AD NIGMA - ETL2 ERROR ' . date( "Y-m-d H:i:s", $this->_timestamp );


		\ini_set('memory_limit','3000M');
		\set_time_limit(0);

        $this->_count = 0;
	}


    public function actionIndex( )
    {
        try
        {
            $this->campaigns();            
        }
        catch ( Exception $e )
        {
            $msg .= "ETL CAMPAINGS ERROR: ".$e->getCode().'<hr>';
            $msg .= $e->getMessage();

            if ( !$this->_noalerts )
                $this->_sendMail ( self::ALERT_FROM, self::ALERT_TO, $this->_alertSubject, $msg );

            die($msg);
        }

        try
        {
            $this->placements();
        }
        catch ( Exception $e )
        {
            $msg .= "ETL PLACEMENTS ERROR: ".$e->getCode().'<hr>';
            $msg .= $e->getMessage();

            if ( !$this->_noalerts )
                $this->_sendMail ( self::ALERT_FROM, self::ALERT_TO, $this->_alertSubject, $msg );

            die($msg);           
        }
        
        try
        {
            $this->imps();
        } 
        catch (Exception $e) {
            $msg .= "ETL IMPRESSIONS ERROR: ".$e->getCode().'<hr>';
            $msg .= $e->getMessage();

            if ( !$this->_noalerts )
                $this->_sendMail ( self::ALERT_FROM, self::ALERT_TO, $this->_alertSubject, $msg );

            die($msg);
        }


        try
        {
            $this->convs();
        } 
        catch (Exception $e) {
            $msg .= "ETL CONVERSIONS ERROR: ".$e->getCode().'<hr>';
            $msg .= $e->getMessage();

            if ( !$this->_noalerts )
                $this->_sendMail ( self::ALERT_FROM, self::ALERT_TO, $this->_alertSubject, $msg );

            die($msg);
        }

        try
        {
            $this->_updatePlacements();
        } 
        catch (Exception $e) {
            $msg .= "ETL PLACEMENT UPDATE ERROR: ".$e->getCode().'<hr>';
            $msg .= $e->getMessage();

            if ( !$this->_noalerts )
                $this->_sendMail ( self::ALERT_FROM, self::ALERT_TO, $this->_alertSubject, $msg );

            die($msg);
        }

        try
        {
            $this->userAgents();
        } 
        catch (Exception $e) {
            $msg .= "ETL USER AGENT ERROR: ".$e->getCode().'<hr>';
            $msg .= $e->getMessage();

            if ( !$this->_noalerts )
                $this->_sendMail ( self::ALERT_FROM, self::ALERT_TO, $this->_alertSubject, $msg );

            die($msg);
        }       
    }


    private function _updatePlacements ( )
    {
        $start   = time();        
        $rows    = \Yii::$app->db->createCommand( $this->_placementSql )->execute();
        $elapsed = time() - $start;

        echo 'Updated Placements: '.$rows.' rows - load time: '.$elapsed.' seg.<hr/>';
    }


    public function convs ( )
    {
        if ( $this->_db )
            $db = $this->_db;
        else
            $db = isset( $_GET['db'] ) ? $_GET['db'] : 'current';

        switch ( $db )
        {
            case 'yesterday':
                $this->_redis->select( $this->_getYesterdayConvDatabase() );
            break;
            case 'current':
                $this->_redis->select( $this->_getCurrentConvDatabase() );
            break;
        } 

    	$start 		   = time();
    	$convIDCount   = $this->_redis->zcard( 'convs' );
    	$queries 	   = ceil( $convIDCount/$this->_objectLimit );
    	$rows   	   = 0;

		// build separate sql queries based on $_objectLimit in order to control memory usage
    	for ( $i=0; $i<=$queries; $i++ )
    	{
    		$rows    += $this->_buildConvQuery ();
    	}

		$elapsed = time() - $start;
		echo 'Conversions: '.$rows.' rows - queries: '.$queries.' - load time: '.$elapsed.' seg.<hr/>';
    }


    private function _buildConvQuery ( )
    {
		$sql 		= '';
		$params 	= [];
		$paramCount = 0;

		$convIDs 	= $this->_redis->zrange( 'convs', 0, $this->_objectLimit );

		// ad each conversion to SQL query
		foreach ( $convIDs as $clickID )
		{
			$convTime    	= $this->_redis->get( 'conv:'.$clickID );

			$param 			= ':i'.$paramCount;
			$params[$param] = $clickID;
			$cost 			= 0;

			$sql .= '
				UPDATE IGNORE F_CampaignLogs cpl 
                    LEFT JOIN F_ClusterLogs cl ON ( cpl.session_hash = cl.session_hash ) 
					LEFT JOIN Campaigns c ON ( cpl.D_Campaign_id = c.id ) 
					LEFT JOIN Placements p ON ( cl.D_Placement_id = p.id ) 
				SET 
					cpl.conv_time="'.\date( 'Y-m-d H:i:s', $convTime ).'", 
					cpl.revenue = c.payout, 
					cl.cost = CASE 
						WHEN p.model = "RS" THEN (c.payout*p.payout)/100 
                        ELSE cl.cost  
                    END 
				WHERE 
					click_id = '.$param.' 
			;';

			$paramCount++;
		}

		if ( $sql != '' )
        {
			$return = \Yii::$app->db->createCommand( $sql )->bindValues( $params )->execute();

            foreach ( $convIDs AS $clickID )
            {
                $this->_redis->zadd( 'loadedconvs', $this->_timestamp, $clickID );
                $this->_redis->zrem( 'convs', $clickID );
            }

            return $return;
        }

        return 0;
    }


    public function imps ( )
    {
    	$this->_campaignLogs();
    	$this->_clusterLogs();
    }


    private function _campaignLogs ( )
    {
        if ( $this->_db )
            $db = $this->_db;
        else
            $db = isset( $_GET['db'] ) ? $_GET['db'] : 'current';

        switch ( $db )
        {
            case 'yesterday':
                $this->_redis->select( $this->_getYesterdayDatabase() );
            break;
            case 'current':
                $this->_redis->select( $this->_getCurrentDatabase() );
            break;
        }   

    	$start 			= time();
    	$clickIDCount   = $this->_redis->zcard( 'clickids' );
    	$queries 		= ceil( $clickIDCount/$this->_objectLimit );
    	$rows 			= 0;


    	// build separate sql queries based on $_objectLimit in order to control memory usage
    	for ( $i=0; $i<$queries; $i++ )
    	{
    		$rows += $this->_buildCampaignLogsQuery( );
    	}

		$elapsed = time() - $start;
		echo 'Campaign Logs: '.$rows.' rows - queries: '.$queries.' - load time: '.$elapsed.' seg.<hr/>';
    }


    private function _buildCampaignLogsQuery ( )
    {
    	$sql = '
    		INSERT INTO F_CampaignLogs (
                click_id,
    			D_Campaign_id,
                session_hash,
    			click_time
    		)
			VALUES  
    	';

    	$values = '';
        
    	$clickIDs = $this->_redis->zrange( 'clickids', 0, $this->_objectLimit );

		if ( $clickIDs )
		{
			// add each campaign log to sql query
    		foreach ( $clickIDs as $clickID )
    		{
    			$campaignLog = $this->_redis->hgetall( 'campaignlog:'.$clickID );

                if ( $campaignLog )
                {
                    if ( $values != '' )
                        $values .= ',';

                    if ( $campaignLog['click_time'] )
                        $campaignLog['click_time'] = '"'.\date( 'Y-m-d H:i:s', $campaignLog['click_time'] ).'"';
                    else
                        $campaignLog['click_time'] = 'NULL';

                    $values .= '( 
                        "'.$clickID.'",
                        '.$campaignLog['campaign_id'].',
                        "'.$campaignLog['session_hash'].'",
                        '.$campaignLog['click_time'].'
                    )';

                    // free memory cause there is no garbage collection until block ends
                    unset( $campaignLog );                    
                }
    		}

    		if ( $values != '' )
    		{
	    		$sql .= $values . ' ON DUPLICATE KEY UPDATE click_time=VALUES(click_time);';

	    		// save on elastic search
	    		// $this->_elasticSearch->bulk($params);
                if ( $this->_showsql || $this->_sqltest )
                    echo '<br><br>SQL: '.$sql. '<br><br>';

                if ( $this->_sqltest )
                    return 0;

                $return = \Yii::$app->db->createCommand( $sql )->execute();         

                if ( $return )
                {
                    foreach ( $clickIDs AS $clickID )
                    {
                        $this->_redis->zadd( 'loadedclicks', $this->_timestamp, $clickID );
                        $this->_redis->zrem( 'clickids', $clickID );
                    }                                        
                }

                return $return;   			
    		}
		}

        unset( $clickIDs );

		return 0;
    }


    private function _clusterLogs ( )
    {
        if ( $this->_db )
            $db = $this->_db;
        else
            $db = isset( $_GET['db'] ) ? $_GET['db'] : 'current';

        switch ( $db )
        {
            case 'yesterday':
                $this->_redis->select( $this->_getYesterdayDatabase() );
            break;
            case 'current':
                $this->_redis->select( $this->_getCurrentDatabase() );
            break;
        }    

    	$start 			     = time();
    	$clusterLogCount     = $this->_redis->zcard( 'sessionhashes' );
    	$queries 		     = ceil( $clusterLogCount/$this->_objectLimit );
    	$rows   		     = 0;    

    	// build separate sql queries based on $_objectLimit in order to control memory usage
    	for ( $i=0; $i<$queries; $i++ )
    	{
    		$rows += $this->_buildClusterLogsQuery();
    	}

		$elapsed = time() - $start;

		echo 'Cluster Logs: '.$rows.' rows - queries: '.$queries.' - load time: '.$elapsed.' seg.<hr/>';
    }


    private function _buildClusterLogsQuery ( )
    {
    	$sql = '
    		INSERT INTO F_ClusterLogs (
                session_hash,
    			D_Placement_id,
                pub_id,
                subpub_id,
    			cluster_id,
                cluster_name,
    			imps,
    			imp_time,
    			cost,
                exchange_id,
    			country,
    			connection_type,
    			carrier,
                device_id,
    			device,
    			device_model,
    			device_brand,
    			os,
    			os_version,
    			browser,
    			browser_version
    		)
			VALUES  
    	';

    	$values = '';    		

		$sessionHashes = $this->_redis->zrange( 'sessionhashes', 0, $this->_objectLimit );

		if ( $sessionHashes )
		{
            $placements          = [];
            $clusters            = [];
            $this->_placementSql = '';

			// add each clusterLog to sql query
    		foreach ( $sessionHashes as $sessionHash )
    		{
                $clusterLog = $this->_redis->hgetall( 'clusterlog:'.$sessionHash );

                if ( $clusterLog )
                {
                    if ( $values != '' )
                        $values .= ',';

                    /*
                    if ( !\filter_var($clusterLog['ip'], \FILTER_VALIDATE_IP) || !preg_match('/^[a-zA-Z]{2}$/', $clusterLog['country']) )
                    {
                        $ips = \explode( ',', $clusterLog['ip'] );
                        $clusterLog['ip'] = $ips[0];

                        $location = new \IP2Location(Yii::app()->params['ipDbFile'], \IP2Location::FILE_IO);
                        $ipData      = $location->lookup($clusterLog['ip'], \IP2Location::ALL);

                        $clusterLog['carrier'] = $ipData->mobileCarrierName;
                        $clusterLog['country'] = $ipData->countryCode;

                        if ( $ipData->mobileCarrierName == '-' )
                            $clusterLog['connection_type'] = 'WIFI';
                        else
                            $clusterLog['connection_type'] = 'MOBILE';
                    }
                    */

                    if ( !$clusterLog['placement_id'] || $clusterLog['placement_id']=='' || !preg_match( '/^[0-9]+$/',$clusterLog['placement_id'] ) )
                        $clusterLog['placement_id'] = 'NULL';

                    if ( $clusterLog['pub_id'] && $clusterLog['pub_id']!='' )
                        $clusterLog['pub_id'] = '"'.$this->_escapeSql( $clusterLog['pub_id'] ).'"';
                    else
                        $clusterLog['pub_id'] = 'NULL';


                    if ( $clusterLog['subpub_id'] && $clusterLog['subpub_id']!='' )
                        $clusterLog['subpub_id'] = '"'.$this->_escapeSql( $clusterLog['subpub_id'] ).'"';
                    else
                        $clusterLog['subpub_id'] = 'NULL';


                    if ( $clusterLog['exchange_id'] && $clusterLog['exchange_id']!='' )
                        $clusterLog['exchange_id'] = '"'.$this->_escapeSql( $clusterLog['exchange_id'] ).'"';
                    else
                        $clusterLog['exchange_id'] = 'NULL';


                    if ( $clusterLog['country'] && $clusterLog['country']!='' )
                        $clusterLog['country'] = '"'.strtoupper($clusterLog['country']).'"';
                    else
                        $clusterLog['country'] = 'NULL';


                    if ( $clusterLog['carrier'] && $clusterLog['carrier']!='' )
                        $clusterLog['carrier'] = '"'.$this->_escapeSql( $clusterLog['carrier'] ).'"';
                    else
                        $clusterLog['carrier'] = 'NULL';


                    if ( $clusterLog['connection_type'] && $clusterLog['connection_type']!='' )
                    {
                        if ( $clusterLog['connection_type']== '3g' || $clusterLog['connection_type']== '3G' )
                            $clusterLog['connection_type']= 'MOBILE';

                        $clusterLog['connection_type'] = '"'.strtoupper($clusterLog['connection_type']).'"';
                    }
                    else
                        $clusterLog['connection_type'] = 'NULL';


                    if ( $clusterLog['device_id'] && $clusterLog['device_id']!='' )
                        $clusterLog['device_id'] = '"'.$this->_escapeSql( $clusterLog['device_id'] ).'"';
                    else
                        $clusterLog['device_id'] = 'NULL';


                    if ( !isset($clusterLog['device']) || !$clusterLog['device'] || $clusterLog['device']=='' )
                        $clusterLog['device'] = 'NULL';
                    else
                        $clusterLog['device'] = '"'.ucwords(strtolower($clusterLog['device'])).'"';


                    if ( isset($clusterLog['device_brand']) && $clusterLog['device_brand'] && $clusterLog['device_brand']!='' )
                        $clusterLog['device_brand'] = '"'.$this->_escapeSql( $clusterLog['device_brand'] ).'"';
                    else
                        $clusterLog['device_brand'] = 'NULL';


                    if ( isset($clusterLog['device_model']) && $clusterLog['device_model'] && $clusterLog['device_model']!='' )
                        $clusterLog['device_model'] = '"'.$this->_escapeSql( $clusterLog['device_model'] ).'"';
                    else
                        $clusterLog['device_model'] = 'NULL';


                    if ( isset($clusterLog['os']) && $clusterLog['os'] && $clusterLog['os']!='' )
                        $clusterLog['os'] = '"'.$this->_escapeSql( $clusterLog['os'] ).'"';
                    else
                        $clusterLog['os'] = 'NULL';


                    if ( isset($clusterLog['os_version']) && $clusterLog['os_version'] && $clusterLog['os_version']!='' )
                        $clusterLog['os_version'] = '"'.$this->_escapeSql( $clusterLog['os_version'] ).'"';
                    else
                        $clusterLog['os_version'] = 'NULL';   


                    if ( isset($clusterLog['browser']) && $clusterLog['browser'] && $clusterLog['browser']!='' )
                        $clusterLog['browser'] = '"'.$this->_escapeSql( $clusterLog['browser'] ).'"';
                    else
                        $clusterLog['browser'] = 'NULL';  

                    if ( isset($clusterLog['browser_version']) && $clusterLog['browser_version'] && $clusterLog['browser_version']!='' )
                        $clusterLog['browser_version'] = '"'.$this->_escapeSql( $clusterLog['browser_version'] ).'"';
                    else
                        $clusterLog['browser_version'] = 'NULL';


                    if ( $clusterLog['device']=='Phablet' || $clusterLog['device']=='Smartphone' )
                        $clusterLog['device'] = '"mobile"';

                    $values .= '( 
                        "'.$sessionHash.'",
                        '.$clusterLog['placement_id'].',
                        '.$clusterLog['pub_id'].',
                        '.$clusterLog['subpub_id'].',
                        '.$clusterLog['cluster_id'].',
                        "'.$clusterLog['cluster_name'].'",
                        '.$clusterLog['imps'].',
                        "'.\date( 'Y-m-d H:i:s', $clusterLog['imp_time'] ).'",
                        '.$clusterLog['cost'].',
                        '.$clusterLog['exchange_id'].',
                        '.$clusterLog['country'].',
                        '.$clusterLog['connection_type'].',
                        '.$clusterLog['carrier'].',
                        '.$clusterLog['device_id'].',
                        '.$clusterLog['device'].',
                        '.$clusterLog['device_model'].',
                        '.$clusterLog['device_brand'].',
                        '.$clusterLog['os'].',
                        '.$clusterLog['os_version'].',
                        '.$clusterLog['browser'].',
                        '.$clusterLog['browser_version'].'
                    )';

                    // add placements to placements update query
                    if ( !\in_array( $clusterLog['placement_id'], $placements ) )
                    {
                        $placements[]      = $clusterLog['placement_id'];
                        $health_check_imps = $this->_redis->hget( 'placement:'.$clusterLog['placement_id'], 'imps' );

                        if ( $health_check_imps && $health_check_imps>0 )
                            $this->_placementSql     .= 'UPDATE Placements SET imps='.$health_check_imps.' WHERE id='.$clusterLog['placement_id'].';';
                        $this->_count++;
                    }

                    // save reporting multiselect data
                    \Yii::$app->redis->sadd( 'carriers', $clusterLog['carrier']  );
                    \Yii::$app->redis->sadd( 'countries', $clusterLog['country']  );

                    // save cluster name to be used in reporting multiselect
                    if ( !\in_array( $clusterLog['cluster_id'], $clusters ) )
                    {
                        \Yii::$app->redis->hset( 'clusternames', $clusterLog['cluster_id'], $clusterLog['cluster_name']  );
                    }

                    // free memory because there is no garbage collection until block ends
                    unset ( $clusterLog );                                        
                }
    		}

    		if ( $values != '' )
    		{
	    		$sql .= $values . ' ON DUPLICATE KEY UPDATE cost=VALUES(cost), imps=VALUES(imps);';

                if ( $this->_showsql || $this->_sqltest )
                    echo '<br><br>SQL: '.$sql.'<br><br>';

                if ( $this->_sqltest )
                    return 0;

	    		$return = \Yii::$app->db->createCommand( $sql )->execute();			

                if ( $return )
                {
                    foreach ( $sessionHashes AS $sessionHash )
                    {
                        $this->_redis->zadd( 'loadedlogs', $this->_timestamp, $sessionHash );

                        $this->_redis->zrem( 'sessionhashes', $sessionHash );
                    }                                        
                }

                return $return;
    		}
		}

		return 0;
    }


    public function userAgents ( )
    {
        $start = time();

        $this->_redis->select(0);

        // load user agents into local cache
        $userAgentIds = $this->_redis->smembers( 'uas' );

        foreach ( $userAgentIds as $id )
        {
            $ua = $this->_redis->hgetall( 'ua:'.$id );

            \Yii::$app->redis->sadd( 'devices', $ua['device']  );
            \Yii::$app->redis->sadd( 'device_brands', $ua['device_brand']  );
            \Yii::$app->redis->sadd( 'device_models', $ua['device_model']  );
            \Yii::$app->redis->sadd( 'os', $ua['os']  );
            \Yii::$app->redis->sadd( 'os_versions', $ua['os_version']  );
            \Yii::$app->redis->sadd( 'browsers', $ua['browser']  );
            \Yii::$app->redis->sadd( 'browser_versions', $ua['browser_version']  );

            // free memory cause there is no garbage collection until block ends
            unset($ua);
        }

        $elapsed = time() - $start;
        echo 'User agents: '.count($userAgentIds).' objects - load time: '.$elapsed.' seg.<hr/>';        
    }


    public function placements ( )
    {
    	$start = time();

    	$sql = '
    		INSERT INTO D_Placement (
                id,
    			Publishers_id,
    			name,
    			Publishers_name,
    			model,
    			status
    		)
    		SELECT
                p.id, 
    			pub.id,
    			p.name,
    			pub.name,
    			p.model,
    			p.status
    		FROM Placements AS p 
    		LEFT JOIN Publishers AS pub ON ( p.Publishers_id = pub.id ) 
    		ON DUPLICATE KEY UPDATE
    			Publishers_id = pub.id, 
    			name = p.name, 
    			Publishers_name = pub.name, 
    			model = p.model, 
    			status = p.status 
    	;';

    	$rows = \Yii::$app->db->createCommand( $sql )->execute();

		$elapsed = time() - $start;

		echo 'Placements: '.$rows.' - Elapsed time: '.$elapsed.' seg.<hr/>';
    }


    public function campaigns ( )
    {
    	$start = time();

    	$sql = '
    		INSERT INTO D_Campaign (
                id,
    			Affiliates_id,
    			name,
    			Affiliates_name
    		)
    		SELECT 
                c.id,
    			a.id,
    			c.name,
    			a.name
    		FROM Campaigns AS c 
    		LEFT JOIN Affiliates AS a ON ( c.Affiliates_id = a.id )
    		ON DUPLICATE KEY UPDATE 
    			Affiliates_id = a.id,
    			name = c.name,
    			Affiliates_name = a.name
    	;';

    	$rows = \Yii::$app->db->createCommand( $sql )->execute();

		$elapsed = time() - $start;

		echo 'Campaigns: '.$rows.' rows - Elapsed time: '.$elapsed.' seg.<hr/>';
    }


    private function _getYesterdayDatabase (  )
    {
        switch ( floor(($this->_timestamp/60/60/24))%2+1 )
        {
            case 1:
                return 2;
            break;
            case 2:
                return 1;
            break;
        }
    }

    private function _getYesterdayConvDatabase (  )
    {
        switch ( floor(($this->_timestamp/60/60/24))%2+3 )
        {
            case 1:
                return 2;
            break;
            case 2:
                return 1;
            break;
        }
    }


    private function _getCurrentDatabase (  )
    {
        return floor(($this->_timestamp/60/60/24))%2+1;
    }


    private function _getCurrentConvDatabase (  )
    {
        return floor(($this->_timestamp/60/60/24))%2+3;
    }


    private function _escapeSql( $sql )
    {
        return preg_replace(
            [
                '/(\\\\)/',
                '/(NUL)/',
                '/(BS)/',
                '/(TAB)/',
                '/(LF)/',
                '/(CR)/',
                '/(SUB)/',
                '/(%)/',                
                "/(')/",
                '/(")/',
                '/(_)/'
            ],
            [
                '\\\\\\',
                '\0',
                '\b',
                '\t',
                '\n',
                '\r',
                '\Z',
                '\%',                
                "\\'",
                '\"',
                '\\_'
            ],
            $sql
        );
    }


    public function actionStats ( )
    {
        $date = isset($_GET['date']) && $_GET['date'] ? $_GET['date'] : date( 'Y-m-d' );

        $sql = '
            INSERT IGNORE INTO Dashboard (                
                country,
                date,
                imps,
                unique_users,
                installs,          
                cost,
                revenue
            ) 
            SELECT * FROM (
                SELECT 
                    cl.country AS country, 
                    cl.imp_time AS date, 
                    sum( cl.imps ) AS imps, 
                    count( cl.session_hash ) AS unique_users,
                    sum(CASE 
                        WHEN date(cp.conv_time)=date(cl.imp_time) THEN 1 
                        ELSE 0 
                    END) AS installs, 
                    sum( cl.cost ) AS cost,
                    sum( cp.revenue ) AS revenue 
                FROM F_CampaignLogs cp 
                RIGHT JOIN F_ClusterLogs cl ON ( cp.session_hash = cl.session_hash ) 
                WHERE date(cl.imp_time)="'.$date.'" 
                GROUP BY date(cl.imp_time), cl.country 
            ) AS r
            ON DUPLICATE KEY UPDATE 
                imps = r.imps, 
                unique_users = r.unique_users, 
                installs = r.installs, 
                cost = r.cost, 
                country = r.country,
                revenue = r.revenue;
        ';

        \Yii::$app->db->createCommand( $sql )->execute();
    }

/*
    public function actionDailymaintenance ( )
    {
        $etl     = isset( $_GET['etl'] ) && $_GET['etl'] ? false : true;

        $showAll = isset( $_GET['showall'] ) && $_GET['showall'] ? true : false;

        $flush = isset( $_GET['flush'] ) && $_GET['flush'] ? true : false;

        $this->_error = false;

        $this->_redis->select( $this->_getYesterdayDatabase() );
        
        if ( $flush || $etl )
        {
            $this->actionIndex();
        }

        $dates     = $this->_redis->smembers( 'dates' );
        $html      = '';

        foreach ( $dates as $date )
        {   
            $miniDate  = date( 'Ymd', strtotime($date) );
            $logCount  = $this->_redis->zcard( 'tags:'.$miniDate );
            $queries   = (int)ceil( $logCount/($this->_objectLimit/2) );

            for ( $i=0; $i<$queries; $i++ )
            {
                $html .= $this->_maintenanceQuery( 
                    $date,
                    $miniDate,
                    $showAll 
                );
            }

            unset( $logCount );
        }

        if ( $this->_error || $html != '' )
        {
            $html     = '
                <html>
                    <head>
                    </head>
                    <body>
                        <table>
                            <thead>
                                <td>STATUS</td>
                                <td>DATE</td>
                                <td>TAG ID</td>
                                <td>REDIS IMPS</td>
                                <td>MYSQL IMPS</td>
                                <td>REDIS COST</td>
                                <td>MYSQL COST</td>
                                <td>REDIS REVENUE</td>
                                <td>MYSQL REVENUE</td>
                            </thead>
                            <tbody>'.$html.'</tbody>
                        </table>
                    </body>
                </html>
            ';
            
            echo $html;

            if ( !$this->_noalerts )
                $this->_sendMail ( 
                    self::ALERT_FROM, 
                    self::ALERT_TO, 
                    'AD NIGMA - TRAFFIC COMPARE ERROR ('.$date.')', 
                    $html 
                );
        }
        else
        {
            if (  $flush )
            {
                $this->_redis->flushdb();
            }

            echo ( 'todo bien piola' );
        }
    }


    private function _maintenanceQuery ( $date, $miniDate, $showAll )
    {
        $html      = '';
        $limit     = ceil($this->_objectLimit/2);
        $redisTags = $this->_redis->zrange( 'tags:'.$miniDate, 0, $limit );

        $sql       = 'SELECT DISTINCT D_Demand_id AS id, sum(imps) AS imps, sum(cost) AS cost, sum(revenue) AS revenue FROM F_Imp_Compact WHERE date(date_time)="'.$date.'" GROUP BY D_Demand_id LIMIT '. $limit;

        $sql = '
            SELECT 
                DISTINCT D_Campaign AS id,
                sum(cl.imps) AS imps,
                sum(cl.cost) AS cost,

            FROM  F_CampaignLogs cpl
            RIGHT JOIN F_ClusterLogs cl ON (cl.session_hash = cpl.session_hash)
            WHERE date(date_time)="'.$date.'" GROUP BY D_Demand_id LIMIT '. $limit            
        ';

        $tmpSqlTags   = Yii::app()->db->createCommand( $sql )->queryAll();        
        $sqlTags = [];

        foreach ( $tmpSqlTags as $tmpSqlTag )
        {
            $sqlTagId           = $tmpSqlTag['id'];
            $sqlTags[$sqlTagId] = [
                'imps'     => $tmpSqlTag['imps'],
                'cost'     => $tmpSqlTag['cost'],
                'revenue'  => $tmpSqlTag['revenue']
            ];
        }

        unset ( $tmpSqlTags );

        foreach ( $redisTags as $tagId )
        {
            $redisTag = $this->_redis->hgetall( 'req:t:'.$tagId.':'.$miniDate );

            if ( !isset( $sqlTags[$tagId] ) )
            {
                $sqlTags[$tagId] = [
                    'imps'      => 0,
                    'cost'      => 0.00,
                    'revenue'   => 0.00
                ];
            }
            if ( !$redisTag )
            {
                $html .= '
                    <tr>
                        <td style="color:#FC5005;>NO DATA</td>
                        <td>'.$date.'</td>
                        <td>'.$tagId.'</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                    </tr>
                ';

                $this->_error = true;
                continue;
            }

            if ( 
                $redisTag['imps']       != $sqlTags[$tagId]['imps'] 
                || $redisTag['cost']    != $sqlTags[$tagId]['cost'] 
                || $redisTag['revenue'] != $sqlTags[$tagId]['revenue']
            )
            {
                $html .= '
                    <tr>
                        <td style="color:#B40303;">DISCREPANCY</td>
                        <td>'.$date.'</td>
                        <td>'.$tagId.'</td>
                        <td>'.$redisTag['imps'].'</td>
                        <td>'.$sqlTags[$tagId]['imps'].'</td>
                        <td>'.$redisTag['cost'].'</td>
                        <td>'.$sqlTags[$tagId]['cost'].'</td>
                        <td>'.$redisTag['revenue'].'</td>
                        <td>'.$sqlTags[$tagId]['revenue'].'</td>
                    </tr>
                ';

                $this->_error = true;
            }

            if ( $showAll )
            {
                $html .= '
                    <tr>
                        <td style="color:#079005;">MATCH</td>
                        <td>'.$date.'</td>
                        <td>'.$tagId.'</td>
                        <td>'.$redisTag['imps'].'</td>
                        <td>'.$sqlTags[$tagId]['imps'].'</td>
                        <td>'.$redisTag['cost'].'</td>
                        <td>'.$sqlTags[$tagId]['cost'].'</td>
                        <td>'.$redisTag['revenue'].'</td>
                        <td>'.$sqlTags[$tagId]['revenue'].'</td>
                    </tr>
                ';                
            }

            unset( $redisTag );
        }

        return $html;
    }  
    */             

}