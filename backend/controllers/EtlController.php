<?php

namespace backend\controllers;

//require_once '../../vendor/ip2location/ip2location-php/IP2Location.php';
//use Elasticsearch\ClientBuilder;
use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\DeviceParserAbstract;
use DeviceDetector\Parser\Client\ClientParserAbstract;
use app\models;
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

        try
        {
            $this->actionPopulatefilters();
        } 
        catch (Exception $e) {
            $msg .= "REPORTING FILTERS POPULATE ERROR: ".$e->getCode().'<hr>';
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
                    \Yii::$app->redis->zadd( 'carriers', 0, $clusterLog['carrier']  );
                    \Yii::$app->redis->zadd( 'countries', 0, $clusterLog['country']  );
                    \Yii::$app->redis->zadd( 'exchange_ids', 0, $clusterLog['exchange_id']  );
                    \Yii::$app->redis->zadd( 'pub_ids', 0, $clusterLog['pub_id']  );
                    \Yii::$app->redis->zadd( 'subpub_ids', 0, $clusterLog['subpub_id']  );
                    \Yii::$app->redis->zadd( 'device_ids', 0, $clusterLog['device_id']  );                    
                    // free memory 
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

            // guarda en redis con el component de yii, configurado para guardar en la db 9
            \Yii::$app->redis->zadd( 'devices', 0, $ua['device']  );
            \Yii::$app->redis->zadd( 'device_brands', 0, $ua['device_brand']  );
            \Yii::$app->redis->zadd( 'device_models', 0, $ua['device_model']  );
            \Yii::$app->redis->zadd( 'os', 0, $ua['os']  );
            \Yii::$app->redis->zadd( 'os_versions', 0, $ua['os_version']  );
            \Yii::$app->redis->zadd( 'browsers', 0, $ua['browser']  );
            \Yii::$app->redis->zadd( 'browser_versions', 0, $ua['browser_version']  );

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


    public function actionPopulatefilters ( )
    {
        $this->actionPopulateclusterfilters();
        $this->actionPopulatecampaignfilters();
        $this->actionPopulateplacementfilters();
        $this->actionPopulateaffiliatefilters();
        $this->actionPopulatepublisherfilters();
    }


    public function actionPopulateclusterfilters ( )
    {
        $start = time();

        $clusters = models\Clusters::find()->all();

        foreach ( $clusters as $model )
        {
            if (strlen($model->name)>31)
                $fill = '...';
            else
                $fill = '';

            $data = [
                'name'  => substr($model->name,0,30) . $fill . ' ('.$model->id.')',
                'id'    => $model->id
            ];

            // guarda en redis con el component de yii, configurado para guardar en la db 9
            \Yii::$app->redis->zadd( 'clusters', 0,  json_encode($data) );
        }

        $elapsed = time() - $start;

        echo 'Cluster filters: '.count($clusters).' rows - Elapsed time: '.$elapsed.' seg.<hr/>';
    }


    public function actionPopulatecampaignfilters ( )
    {
        $start = time();

        $campaigns = models\Campaigns::find()->all();

        foreach ( $campaigns as $model )
        {
            if (strlen($model->name)>31)
                $fill = '...';
            else
                $fill = '';

            $data = [
                'name'  => substr($model->name,0,30) . $fill . ' ('.$model->id.')',
                'id'    => $model->id
            ];

            // guarda en redis con el component de yii, configurado para guardar en la db 9
            \Yii::$app->redis->zadd( 'campaigns', 0,  json_encode($data) );
        }

        $elapsed = time() - $start;

        echo 'Campaigns filters: '.count($campaigns).' rows - Elapsed time: '.$elapsed.' seg.<hr/>';
    } 


    public function actionPopulateaffiliatefilters ( )
    {
        $start = time();

        $affiliates = models\Affiliates::find()->all();

        foreach ( $affiliates as $model )
        {
            if (strlen($model->name)>31)
                $fill = '...';
            else
                $fill = '';

            $data = [
                'name'  => substr($model->name,0,30) . $fill . ' ('.$model->id.')',
                'id'    => $model->id
            ];

            // guarda en redis con el component de yii, configurado para guardar en la db 9
            \Yii::$app->redis->zadd( 'affiliates', 0,  json_encode($data) );
        }

        $elapsed = time() - $start;

        echo 'Affiliate filters: '.count($affiliates).' rows - Elapsed time: '.$elapsed.' seg.<hr/>';
    }        

    public function actionPopulateplacementfilters ( )
    {
        $start = time();

        $placements = models\Placements::find()->all();

        foreach ( $placements as $model )
        {
            if (strlen($model->name)>31)
                $fill = '...';
            else
                $fill = '';

            $data = [
                'name'  => substr($model->name,0,30) . $fill . ' ('.$model->id.')',
                'id'    => $model->id
            ];

            // guarda en redis con el component de yii, configurado para guardar en la db 9
            \Yii::$app->redis->zadd( 'placements', 0,  json_encode($data) );
        }

        $elapsed = time() - $start;

        echo 'Placements filters: '.count($placements).' rows - Elapsed time: '.$elapsed.' seg.<hr/>';
    }

    public function actionPopulatepublisherfilters ( )
    {
        $start = time();

        $publishers = models\Publishers::find()->all();

        foreach ( $publishers as $model )
        {
            if (strlen($model->name)>31)
                $fill = '...';
            else
                $fill = '';
                            
            $data = [
                'name'  => substr($model->name,0,30) . $fill . ' ('.$model->id.')',
                'id'    => $model->id
            ];

            // guarda en redis con el component de yii, configurado para guardar en la db 9
            \Yii::$app->redis->zadd( 'publishers', 0,  json_encode($data) );
        }

        $elapsed = time() - $start;

        echo 'Publishers filters: '.count($publishers).' rows - Elapsed time: '.$elapsed.' seg.<hr/>';
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


    private function _sendmail ( $from, $to, $subject, $body )
    {
        $headers = 'From:'.$from.'\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset="UTF-8"\r\n';

        if ( !mail($to, $subject, $body, $headers ) )
        {
            $data = 'To: '.$to.'\nSubject: '.$subject.'\nFrom:'.$from.'\n'.$body;

            $command = 'echo -e "'.$data.'" | sendmail -bm -t -v';
            $command = '
                export MAILTO="'.$to.'"
                export FROM="'.$from.'"
                export SUBJECT="'.$subject.'"
                export BODY="'.$body.'"
                (
                 echo "From: $FROM"
                 echo "To: $MAILTO"
                 echo "Subject: $SUBJECT"
                 echo "MIME-Version: 1.0"
                 echo "Content-Type: text/html; charset=UTF-8"
                 echo $BODY
                ) | /usr/sbin/sendmail -F $MAILTO -t -v -bm
            ';

            shell_exec( $command );             
        }           
    }


    public function actionPopulatecache ( )
    {
        $this->actionPopulateclusters();
        $this->actionPopulateplacements();
    }


    public function actionPopulateclusters ( )
    {
       $this->_redis->select( 0 );

        $start = time();

        $clusters = models\Clusters::find()->all();

        foreach ( $clusters as $model )
        {
            if ( !$model->connection_type || $model->connection_type=='' )
                $model->connection_type = null;

            if ( !$model->os || $model->os=='' )
                $model->os = null;

            if ( !$model->country || $model->country=='' )
                $model->country = null;

            $carrierName = $model->carriers ? $model->carriers->carrier_name : null;            
            $this->_redis->hmset( 'cluster:'.$model->id,  [
                'name'              => $model->name,
                'country'           => strtolower($model->country),
                'os'                => $model->os,
                'device_type'       => strtolower($model->device_type), 
                'connection_type'   => strtolower($model->connection_type), 
                'os_version'        => strtolower($model->os_version), 
                'carrier'           => strtolower($carrierName), 
                'static_cp_land'    => $model->staticCampaigns->landing_url,
                'static_cp_300x250' => $model->staticCampaigns->creative_300x250,
                'static_cp_320x50'  => $model->staticCampaigns->creative_320x50,
            ]);

            $campaignsModel = new models\Campaigns;

            $this->_redis->del( 'clusterlist:'.$model->id );

            $campaigns = $campaignsModel->getByCluster( $model->id );

            foreach ( $campaigns as $campaign )
            {
                switch ( $campaign->status )
                {
                    case 'active':
                        $status = 1;
                    break;
                    default:
                        $status = 0;
                    break;
                }
                $this->_redis->zadd( 'clusterlist:'.$model->id, $status, $campaign->id );
            }
        }

        $elapsed = time() - $start;

        echo 'Clusters cached: '.count($clusters).' - Elapsed time: '.$elapsed.' seg.<hr/>';
    }


    public function actionPopulateplacements ( )
    {
        $this->_redis->select( 0 );

        $start = time();

        $placements = models\Placements::find()->all();

        foreach ( $placements as $model )
        {
            $origin = $this->_redis->hgetall('placement:'.$model->id);

            if ( $origin )
            {
                $imps   = (int)$origin['imps'];
                $hcimps = (int)$origin['health_check_imps'];
            }
            else
            {
                $imps   = (int)$model->imps;
                $hcimps = (int)$model->health_check_imps;                
            }

            $this->_redis->hmset( 'placement:'.$model->id, [
                'frequency_cap'     => $model->frequency_cap,
                'payout'            => $model->payout,
                'model'             => $model->model,
                'cluster_id'        => isset($model->clusters->id) ? $model->clusters->id : null,
                'status'            => $model->status,
                'size'              => $model->size,
                'imps'              => $imps,
                'health_check_imps' => $hcimps
            ]);
        }

        $elapsed = time() - $start;

        echo 'Placements cached: '.count($placements).' - Elapsed time: '.$elapsed.' seg.<hr/>';
    }        

}