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

class RedshiftetlController extends \yii\web\Controller
{
    const ALERT_FROM         = 'Splad - ETL Controller<no-reply@spladx.co>';
    const ALERT_TO           = 'dev@splad.co,apastor@splad.co';
    
    const NO_CONV_LIMIT      = 20000; // imps
    const CONV_WAIT_TIME     = 2; // days
    const AUTOARCHIVE_TIME   = 14; // days

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
    private $_skipmarks;
    private $_pdo;

    private $_count;


    public function __construct ( $id, $module, $config = [] )
    {
        parent::__construct( $id, $module, $config );

        $this->_redis = new \Predis\Client( \Yii::$app->params['predisConString'] );

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

        $this->_showsql       = isset( $_GET['showsql'] ) && $_GET['showsql'] ? true : false;
        $this->_noalerts      = isset( $_GET['noalerts'] ) && $_GET['noalerts'] ? true : false;
        $this->_sqltest       = isset( $_GET['sqltest'] ) && $_GET['sqltest'] ? true : false;     
        $this->_skipmarks     = isset( $_GET['skipmarks'] ) && $_GET['skipmarks'] ? true : false;

        $this->_timestamp     = time();

        $this->_db = isset( $_GET['db'] ) ? $_GET['db'] : 'current';

        $this->_alertSubject  = 'AD NIGMA - ETL2 ERROR ' . date( "Y-m-d H:i:s", $this->_timestamp );

        $this->_pdo = new \PDO( 
            'pgsql:dbname=prod;host=dinky.cspssu6efoeo.us-east-1.redshift.amazonaws.com;port=5439',
            'root',
            'spl4dPr0j3ct'
        );

        $this->_pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);


        ini_set('memory_limit','3000M');
        set_time_limit(0);

        $this->_count = 0;
    }


    public function actionIndex( )
    {
        try
        {
            //$this->actionCampaigns();            
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
            $this->actionPlacements();
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
            //$this->actionImps();
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
            //$this->actionConvs();
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
            //$this->actionCheckclusterconvs();
        } 
        catch (Exception $e) {
            $msg .= "ETL CHECK CLUSTER CONVERSIONS ERROR: ".$e->getCode().'<hr>';
            $msg .= $e->getMessage();

            if ( !$this->_noalerts )
                $this->_sendMail ( self::ALERT_FROM, self::ALERT_TO, $this->_alertSubject, $msg );

            die($msg);
        }        

        try
        {
            //$this->_updatePlacements();
        } 
        catch (Exception $e) {
            $msg .= "ETL PLACEMENT UPDATE ERROR: ".$e->getCode().'<hr>';
            $msg .= $e->getMessage();

            if ( !$this->_noalerts )
                $this->_sendMail ( self::ALERT_FROM, self::ALERT_TO, $this->_alertSubject, $msg );

            die($msg);
        }
             
    }


    private function _updatePlacements ( )
    {
        $start   = time();        
        
        $statement = $this->_pdo->prepare( $this->_placementSql );

        $rows    = $statement->rowCount();

        $elapsed = time() - $start;

        echo 'Updated Placements: '.$rows.' rows - load time: '.$elapsed.' seg.<hr/>';
    }


    public function actionConvs ( )
    {
        switch ( $this->_db )
        {
            case 'yesterday':
                $this->_redis->select( $this->_getYesterdayConvDatabase() );
            break;
            case 'current':
                $this->_redis->select( $this->_getCurrentConvDatabase() );
            break;
        } 

        $start         = time();
        $convIDCount   = $this->_redis->zcard( 'convs' );
        $queries       = ceil( $convIDCount/$this->_objectLimit );
        $rows          = 0;
        $start_at      = 0;
        $end_at        = $this->_objectLimit;        

        // build separate sql queries based on $_objectLimit in order to control memory usage
        for ( $i=0; $i<=$queries; $i++ )
        {
            $rows    += $this->_buildConvQuery ( $start_at, $end_at );

            $start_at += $this->_objectLimit-1;
            $end_at   += $this->_objectLimit;            
        }

        $elapsed = time() - $start;
        echo 'Conversions: '.$rows.' rows - queries: '.$queries.' - load time: '.$elapsed.' seg.<hr/>';
    }


    private function _buildConvQuery ( $start_at, $end_at )
    {
        $sql              = '';
        $params           = [];
        $paramCount       = 0;

        $convs  = $this->_redis->zrange( 
            'convs', 
            0, 
            $this->_objectLimit, 
            [
                'WITHSCORES' => true
            ]            
        );

        // ad each conversion to SQL query
        foreach ( $convs as $clickID => $convTime )
        {
            $param          = ':i'.$paramCount;
            $params[$param] = $clickID;
            $cost           = 0;

            $sql .= '
                UPDATE IGNORE F_CampaignLogs cpl 
                    LEFT JOIN F_ClusterLogs cl ON ( cpl.session_hash = cl.session_hash ) 
                    LEFT JOIN D_Campaign c ON ( cpl.D_Campaign_id = c.id ) 
                    LEFT JOIN D_Placement p ON ( cl.D_Placement_id = p.id ) 
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
	        $statement = $this->_pdo->prepare( $sql );

	        $rows    = $statement->rowCount();

	        foreach ( $params as $param => $value )
	        {
	        	$statement->bindValue( $param, $value );
	        } 

            // free RAM
            unset( $sql );
            unset( $params );

            // re-enable autostopped cluster assignments for campaigns which conversion/s arrived in time
            $clickIDs = '';

            foreach ( $convs AS $clickID => $convTime )
            {
                if ( $clickIDs != '' )
                    $clickIDs .= ',';

                $clickIDs .= '"'.$clickID.'"';
            }

            $sql = '
                SELECT
                    c.D_Campaign_id AS campaign_id,                      
                    cl.cluster_id AS cluster_id
                FROM F_CampaignLogs c 
                LEFT JOIN F_ClusterLogs cl ON cl.session_hash=c.session_hash 
                WHERE 
                    c.click_time >= date(c.conv_time - INTERVAL '.self::CONV_WAIT_TIME.' DAY) 
                    AND c.click_id IN ( '.$clickIDs.' ) 
                GROUP BY c.D_Campaign_id, cl.cluster_id;
            ';

            $chcs = \Yii::$app->db->createCommand( $sql )->queryAll();

            if ( !empty($chcs) )
            {
                $this->_redis->select(0);

                foreach ( $chcs as $chc )
                {
                    $this->_redis->zadd( 'clusterimps:'.$chc['cluster_id'], 0, $chc['campaign_id'] );                         

                    $chci = models\ClustersHasCampaigns::findOne([
                        'Campaigns_id' => $chc['campaign_id'], 
                        'Clusters_id' => $chc['cluster_id'] 
                    ]);

                    if ( $chci && ( $chci->autostopped==true || $chci->autostopped==1 ) )
                    {
                        if ( $chci->prev_freq )
                            $chci->delivery_freq = $chci->prev_freq;
                        else
                            $chci->delivery_freq = 2;

                        $chci->prev_freq     = null;
                        $chci->autostopped   = false; 

                        if ( $chci->save() )
                        {
                            models\CampaignsChangelog::log( $chc['campaign_id'], 'autostop_off', null, $chc['cluster_id'] );

                            if ( $chci->campaigns->app_id )
                            {                                
                                $packageIds = json_decode($chci->campaigns->app_id);

                                foreach ( $packageIds as $packageId )
                                {                                    
                                    $this->_redis->zadd( 
                                        'clusterlist:'.$chc['cluster_id'], 
                                        $chci->delivery_freq, 
                                        $chc['campaign_id'].':'.$chci->campaigns->affiliates->id.':'.$packageId
                                    );
                                }
                            }                               
                        }
                    }

                    unset( $chci );
                }

                switch ( $this->_db )
                {
                    case 'yesterday':
                        $this->_redis->select( $this->_getYesterdayConvDatabase() );
                    break;
                    case 'current':
                        $this->_redis->select( $this->_getCurrentConvDatabase() );
                    break;
                }    
            }                

            // mark conversions as loaded
            if ( !$this->_skipmarks )
            {
	            foreach ( $convs AS $clickID => $convTime )
	            {
	                $this->_redis->zadd( 'loadedconvs', $this->_timestamp, $clickID );
	                $this->_redis->zrem( 'convs', $clickID );	                
	            }            	
            }

            return count($convs);
        }

        return 0;
    }


    public function actionImps ( )
    {
        $this->_campaignLogs();
        $this->_clusterLogs();
    }


    private function _selectTrafficDb ()
    {
        switch ( $this->_db )
        {
            case 'yesterday':
                $this->_redis->select( $this->_getYesterdayDatabase() );
            break;
            case 'current':
                $this->_redis->select( $this->_getCurrentDatabase() );
            break;
        }         
    }


    private function _selectConvDb ()
    {
        switch ( $this->_db )
        {
            case 'yesterday':
                $this->_redis->select( $this->_getYesterdayConvDatabase() );
            break;
            case 'current':
                $this->_redis->select( $this->_getCurrentConvDatabase() );
            break;
        }         
    }


    private function _campaignLogs ( )
    {
        switch ( $this->_db )
        {
            case 'yesterday':
                $this->_redis->select( $this->_getYesterdayDatabase() );
            break;
            case 'current':
                $this->_redis->select( $this->_getCurrentDatabase() );
            break;
        }   

        $start          = time();
        $clickIDCount   = $this->_redis->zcard( 'clickids' );
        $queries        = ceil( $clickIDCount/$this->_objectLimit );
        $rows           = 0;


        // build separate sql queries based on $_objectLimit in order to control memory usage
        for ( $i=0; $i<$queries; $i++ )
        {
            $rows += $this->_buildCampaignLogsQuery( );

        }

        $elapsed = time() - $start;
        echo 'Campaign Logs: '.$rows.' rows - queries: '.$queries.' - load time: '.$elapsed.' seg.<hr/>';
    }


    private function _buildCampaignLogsQuery (  )
    {
        $sql = '
            INSERT INTO stage (
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
                if ( $this->_showsql || $this->_sqltest )
                    echo '<br><br>SQL: '.$sql. '<br><br>';

                if ( $this->_sqltest )
                    return 0;

                try
                {
                	$this->_pdo->beginTransaction();

			        $this->_pdo->exec( '
						CREATE TABLE IF NOT EXISTS stage_cpl (
						  click_id varchar(255) NOT NULL,
						  D_Campaign_id int NOT NULL,
						  D_Placement_id int DEFAULT NULL,
						  session_hash varchar(255) NOT NULL,
						  click_time timestamp NULL DEFAULT NULL,
						  conv_time timestamp NULL DEFAULT NULL,
						  revenue decimal(11,6) DEFAULT 0.000000,
						  PRIMARY KEY (click_id)
						);
			        ' );   

			        $this->_pdo->exec( $sql );

			        $this->_pdo->exec( '
			        	DELETE FROM f_campaignlogs USING stage_cpl WHERE f_campaignlogs.session_hash=stage_cpl.session_hash;
			        ' );

			        $this->_pdo->exec( '
			        	INSERT INTO f_campaignlogs SELECT * FROM stage_cpl;
			        ' );	      

                }
                catch( Exception $e )
                {
                	$this->_pdo->rollback();
                	throw $e;
                	return 0;
                }

                if ( $this->_pdo->commit() )
                {
                	if ( !$this->_skipmarks )
                	{
		                foreach ( $clickIDs AS $clickID )
		                {
		                    $this->_redis->zadd( 'loadedclicks', $this->_timestamp, $clickID );
		                    $this->_redis->zrem( 'clickids', $clickID );
		                }                		
                	}

	                $this->_pdo->exec('DROP TABLE stage_cpl;');

	                return count($clickIDs);
                }

           		return 0;
            }
        }

        unset( $clickIDs );

        return 0;
    }


    private function _clusterLogs ( )
    {
        switch ( $this->_db )
        {
            case 'yesterday':
                $this->_redis->select( $this->_getYesterdayDatabase() );
            break;
            case 'current':
                $this->_redis->select( $this->_getCurrentDatabase() );
            break;
        }    

        $start               = time();
        $clusterLogCount     = $this->_redis->zcard( 'sessionhashes' );
        $queries             = ceil( $clusterLogCount/$this->_objectLimit );
        $rows                = 0;    

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
                clicks,
                imp_time,
                imp_status,
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

        $requestDataSql = '
            INSERT INTO D_RequestData (
                session_hash,
                user_agent,
                ip,
                referer
            )
            VALUES 
        ';

        $values = '';
        $requestValues = '';           

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
                    if ( 
                        !isset($clusterLog['placement_id']) 
                        || !isset( $clusterLog['imps'] )
                    )
                    {
                        $this->_redis->zadd( 'orfanhashes', 0, $sessionHash );
                        continue;
                    }

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


                    if ( isset($clusterLog['idfa']) && $clusterLog['idfa'] && $clusterLog['idfa']!='' )
                        $deviceId = '"'.$this->_escapeSql( $clusterLog['idfa'] ).'"';
                    else if ( isset($clusterLog['gaid']) && $clusterLog['gaid'] && $clusterLog['gaid']!='' )
                        $deviceId = '"'.$this->_escapeSql( $clusterLog['gaid'] ).'"';
                    else if ( $clusterLog['device_id'] && $clusterLog['device_id']!='' )
                        $deviceId = '"'.$this->_escapeSql( $clusterLog['device_id'] ).'"';                    
                    else
                        $deviceId = 'NULL';


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

                    if ( isset( $clusterLog['imp_status'] ) && $clusterLog['imp_status'] && $clusterLog['imp_status']!='' )
                        $impStatus = '"'.$clusterLog['imp_status'].'"';
                    else
                        $impStatus = 'NULL';               

                    if ( isset($clusterLog['clicks']) && $clusterLog['clicks'] )
                        $clicks = $clusterLog['clicks'];
                    else
                        $clicks = 0;

                    $values .= '( 
                        "'.$sessionHash.'",
                        '.$clusterLog['placement_id'].',
                        '.$clusterLog['pub_id'].',
                        '.$clusterLog['subpub_id'].',
                        '.$clusterLog['cluster_id'].',
                        "'.$clusterLog['cluster_name'].'",
                        '.$clusterLog['imps'].',
                        '.$clicks.',
                        "'.\date( 'Y-m-d H:i:s', $clusterLog['imp_time'] ).'",
                        '.$impStatus.',
                        '.$clusterLog['cost'].',
                        '.$clusterLog['exchange_id'].',
                        '.$clusterLog['country'].',
                        '.$clusterLog['connection_type'].',
                        '.$clusterLog['carrier'].',
                        '.$deviceId.',
                        '.$clusterLog['device'].',
                        '.$clusterLog['device_model'].',
                        '.$clusterLog['device_brand'].',
                        '.$clusterLog['os'].',
                        '.$clusterLog['os_version'].',
                        '.$clusterLog['browser'].',
                        '.$clusterLog['browser_version'].'
                    )';


                    if ( $requestValues != '' )
                        $requestValues .= ',';

                    if ( isset($clusterLog['ua']) && $clusterLog['ua'] )
                        $clusterLog['ua'] = '"'.$this->_escapeSql($clusterLog['ua']).'"';
                    else
                        $clusterLog['ua'] = 'NULL';


                    if ( isset($clusterLog['ip']) && $clusterLog['ip'] )
                        $clusterLog['ip'] = '"'.$this->_escapeSql($clusterLog['ip']).'"';
                    else
                        $clusterLog['ip'] = 'NULL';                    


                    if ( isset($clusterLog['referer']) && $clusterLog['referer'] )
                        $clusterLog['referer'] = '"'.$this->_escapeSql($clusterLog['referer']).'"';
                    else
                        $clusterLog['referer'] = 'NULL';

                    $requestValues .= '( 
                        "'.$sessionHash.'",
                        '.$clusterLog['ua'].',
                        '.$clusterLog['ip'].',
                        '.$clusterLog['referer'].'
                    )';                    

                    // add placements to placements update query
                    if ( !\in_array( $clusterLog['placement_id'], $placements ) )
                    {
                        $this->_redis->select(0);

                        $placements[]      = $clusterLog['placement_id'];
                        $placementCache    = $this->_redis->hmget( 'placement:'.$clusterLog['placement_id'], 'imps', 'status' );

                        if ( $placementCache && (int)$placementCache[0]>0 )
                        {
                            $this->_placementSql     .= 'UPDATE Placements SET imps='.(int)$placementCache[0].', status="'.$placementCache[1].'" WHERE id='.$clusterLog['placement_id'].';';
                        }

                        switch ( $this->_db )
                        {
                            case 'yesterday':
                                $this->_redis->select( $this->_getYesterdayDatabase() );
                            break;
                            case 'current':
                                $this->_redis->select( $this->_getCurrentDatabase() );
                            break;
                        }  

                        unset ( $placementCache );
                        $this->_count++;
                    }


                    // free memory 
                    unset ( $clusterLog );
                }
            }

            if ( $values != '' )
            {
                if ( $this->_showsql ){
                    echo '<br><br>SQL: '.$sql.'<br><br>';                    
                    echo '<br><br>SQL: '.$requestDataSql.'<br><br>';                    
                }
                
                if ( $this->_sqltest )
                    return 0;

                try
                {
                	$this->_pdo->beginTransaction();

			        $this->_pdo->exec( '
						CREATE TABLE stage_cll (
						  session_hash varchar(255) NOT NULL,
						  D_Placement_id int NOT NULL,
						  D_Campaign_id int DEFAULT NULL,
						  cluster_id int NOT NULL,
						  cluster_name varchar(255) NOT NULL,
						  imps int NOT NULL,
						  imp_time timestamp NULL DEFAULT NULL,
						  clicks int DEFAULT 0,
						  country char(2) DEFAULT NULL,
						  connection_type varchar(255) DEFAULT NULL,
						  carrier varchar(255) DEFAULT NULL,
						  device varchar(255) DEFAULT "other",
						  device_model varchar(255) DEFAULT NULL,
						  device_brand varchar(255) DEFAULT NULL,
						  os varchar(255) DEFAULT NULL,
						  os_version varchar(255) DEFAULT NULL,
						  browser varchar(255) DEFAULT NULL,
						  browser_version varchar(255) DEFAULT NULL,
						  cost decimal(11,6) DEFAULT 0.000000,
						  exchange_id varchar(255) DEFAULT NULL,
						  pub_id varchar(255) DEFAULT NULL,
						  subpub_id varchar(255) DEFAULT NULL,
						  device_id varchar(255) DEFAULT NULL,
						  imp_status varchar(255) DEFAULT NULL,
						  PRIMARY KEY (session_hash)
						);
			        ' );
  			          

			        $this->_pdo->exec( $sql );

			        $this->_pdo->exec( '
			        	DELETE FROM f_clusterlogs USING stage_cll WHERE f_clusterlogs.session_hash=stage_cll.session_hash;
			        ' );

			        $this->_pdo->exec( '
			        	INSERT INTO f_clusterlogs SELECT * FROM stage_cll;
			        ' );

			        $this->_pdo->exec( '
						CREATE TABLE `stage_req` (
						  session_hash varchar(255) NOT NULL,
						  user_agent varchar(255) DEFAULT NULL,
						  ip char(15) DEFAULT NULL,
						  referer varchar(255) DEFAULT NULL,
						  PRIMARY KEY (session_hash)
						);
			        ' ); 

			        $this->_pdo->exec( $requestDataSql );

			        $this->_pdo->exec( '
			        	DELETE FROM d_requestdata USING stage_req WHERE d_requestdata.session_hash=stage_req.session_hash;
			        ' );

			        $this->_pdo->exec( '
			        	INSERT INTO d_requestdata SELECT * FROM stage_req;
			        ' );	     
                }
                catch( Exception $e )
                {
                	$this->_pdo->rollback();
                	throw $e;
                	return 0;
                }

                if ( $this->_pdo->commit() )
                {
                	if ( !$this->_skipmarks )
                	{
	                    foreach ( $sessionHashes AS $sessionHash )
	                    {
	                        $this->_redis->zadd( 'loadedlogs', $this->_timestamp, $sessionHash );
	                        $this->_redis->zrem( 'sessionhashes', $sessionHash );
	                    }                     		
                	}               

			        $this->_pdo->exec('DROP TABLE stage_cll;');         
			        $this->_pdo->exec('DROP TABLE stage_req;');                	                    

                    return count($sessionHashes);
                }
            }
        }

        return 0;
    }


    public function actionPlacements ( )
    {
        $start = time();

        try
        {
		   $results = \Yii::$app->db->createCommand( '
		        SELECT
		            p.id AS id, 
		            pub.id AS Publishers_id,
		            p.name AS name,
		            pub.name AS Publishers_name,
		            p.model AS model,
		            p.status AS status,
		            p.payout AS payout
		        FROM Placements AS p 
		        LEFT JOIN Publishers AS pub ON ( p.Publishers_id = pub.id ) 
		    ;' )->queryAll();

			if ( $results )
			{
		        $insert = '
		            INSERT INTO stage_placement (
		                id,
		                Publishers_id,
		                name,
		                Publishers_name,
		                model,
		                status,
		                payout
		            ) VALUES         
		        ';		

		        $values = '';

		        foreach ( $results as $row )
		        {
		        	if ( $values != '' )
		        		$values .= ',';

		        	$values .= "(
		        		".$row['id'].",
		        		".$row['Publishers_id'].",
		        		'".$row['name']."',
		        		'".$row['Publishers_name']."',
		        		'".$row['model']."',
		        		'".$row['status']."',
		        		".$row['payout']."
		        	)";
		        }

		        if ( $this->_sqltest || $this->_showsql )
		        	echo $insert . $values . ';' . '<br>';

		        if ( $this->_sqltest )
		        	return 0;

			    $this->_pdo->exec( '
					CREATE TABLE stage_placement (
					  id int NOT NULL,
					  Publishers_id int DEFAULT NULL,
					  name varchar(255) DEFAULT NULL,
					  Publishers_name varchar(255) DEFAULT NULL,
					  model varchar(255) DEFAULT NULL,
					  status varchar(255) DEFAULT NULL,
					  payout decimal(10,8) NOT NULL DEFAULT 0.00000000,
					  PRIMARY KEY (id)
					);
			    ' );		        

			   	$this->_pdo->beginTransaction();

		        $this->_pdo->exec( $insert . $values . ';' );

		        $this->_pdo->exec( '
		        	DELETE FROM d_placement USING stage_placement WHERE stage_placement.id=d_placement.id;
		        ' );

		        $this->_pdo->exec( '
		        	INSERT INTO d_placement SELECT * FROM stage_placement;
		        ' );		

		        if ( $this->_pdo->commit() )
		        {
		        	$this->_pdo->exec('DROP TABLE stage_placement;');  
		        }
			}
			else
			{
				$results = [];
			}       	
        }
        catch ( Exception $e )
        {
        	$this->_pdo->rollback();
        	throw $e;
        }

        $elapsed = time() - $start;

        echo 'Placements: '.count($results).' - Elapsed time: '.$elapsed.' seg.<hr/>';
    }


    public function actionCampaigns( )
    {
        $start         = time();
        $query         = true;
        $rows          = 0;
        $start_at      = 0;
        $limit         = $this->_objectLimit;       

        // build separate sql queries based on $_objectLimit in order to control memory usage
        while ( $query )
        {
            $rows    += $this->_campaignsQuery ( $start_at, $limit );

            if ( $rows == 0 )
            	break;

            $start_at += $this->_objectLimit;
            $limit    += $this->_objectLimit;
        }

        $elapsed = time() - $start;

        echo 'Campaigns: '.$rows.' rows - Elapsed time: '.$elapsed.' seg.<hr/>';    	
    }


    public function _campaignsQuery ( $start_at, $limit )
    {
        try
        {
		   $results = \Yii::$app->db->createCommand( '
		        SELECT
	                c.id AS id,
	                a.id AS Affiliates_id,
	                c.name AS name,
	                a.name AS Affiliates_name,
	                c.payout AS payout
	            FROM Campaigns AS c 
	            LEFT JOIN Affiliates AS a ON ( c.Affiliates_id = a.id )
	            LIMIT '.$start_at.','.$limit.'
		    ;' )->queryAll();

			if ( $results )
			{
		        $insert = '
		            INSERT INTO stage_campaign (
		                id,
		                Affiliates_id,
		                name,
		                Affiliates_name,
		                payout
		            ) VALUES         
		        ';		

		        $values = '';

		        foreach ( $results as $row )
		        {
		        	if ( $values != '' )
		        		$values .= ',';

		        	$values .= "(
		        		".$row['id'].",
		        		".$row['Affiliates_id'].",
		        		'".$this->_escapeSql($row['name'])."',
		        		'".$row['Affiliates_name']."',
		        		".$row['payout'].",
		        	)";
		        }

		        if ( $this->_sqltest || $this->_showsql )
		        	echo $insert . $values . ';<hr>';

		        if  ( $this->_sqltest )
		        	return 0;

			    $this->_pdo->exec( '
					CREATE TABLE stage_campaign (
					  id int NOT NULL,
					  Name varchar(255) NOT NULL,
					  Affiliates_id int NOT NULL,
					  Affiliates_name varchar(255) NOT NULL,
					  payout decimal(10,8) DEFAULT 0.00000000,
					  PRIMARY KEY (id)
					);
			    ' );		        

			   	$this->_pdo->beginTransaction();

		        $this->_pdo->exec( $insert . $values . ';' );

		        $this->_pdo->exec( '
		        	DELETE FROM d_camaign USING stage_campaign WHERE stage_campaign.id=d_camaign.id;
		        ' );

		        $this->_pdo->exec( '
		        	INSERT INTO d_camaign SELECT * FROM stage_campaign;
		        ' );		

		        if ( $this->_pdo->commit() )
		        {
		        	$this->_pdo->exec('DROP TABLE stage_campaign;');  
		        }	    		        	
			}
			else
			{
				$results = [];
			}       	
        }
        catch ( Exception $e )
        {
        	$this->_pdo->rollback();
        	throw $e;
        }

        return count($results);
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
            case 3:
                return 4;
            break;
            case 4:
                return 3;
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


    private function _escapePostgreSql( $sql )
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
                '',
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
                    00 as country,
                    date(if(cp.conv_time is not null, cp.conv_time, cl.imp_time)) AS date, 
                    ceil(sum(if(cl.clicks>0,cl.imps/cl.clicks,cl.imps))) AS imps,
                    ceil(sum(if(cl.clicks>0, 1/cl.clicks, 1))) AS unique_users,
                    count(cp.conv_time)      AS installs,
                    sum(if(cl.clicks>0, cl.cost/cl.clicks, cl.cost)) AS cost, 
                    sum( cp.revenue )        AS revenue 

                FROM F_CampaignLogs cp 

                RIGHT JOIN F_ClusterLogs cl  ON ( cp.session_hash = cl.session_hash ) 
                WHERE date(if(cp.conv_time is not null, cp.conv_time, cl.imp_time))="'.$date.'"

                GROUP BY
                    date(if(cp.conv_time is not null, cp.conv_time, cl.imp_time)) 
            ) AS r

            ON DUPLICATE KEY UPDATE 
                imps = r.imps, 
                unique_users = r.unique_users, 
                installs = r.installs, 
                cost = r.cost, 
                revenue = r.revenue;
        ';

        \Yii::$app->db->createCommand( $sql )->execute();
    }

    public function actionCheckclusterconvs ( $cluster_id = null )
    {
        $disabled = 0;
        $start    = time();

        $this->_redis->select(0);

        $clusterId = isset($_GET['cluster_id']) ? $_GET['cluster_id'] : $cluster_id;

        if ( $clusterId )
        {
            $model = Models\Clusters::findOne( $cluster_id );

            if ( $model )
                $disabled = $this->_checkClusterConvs( $model );
        }
        else
        {
            $clusters = models\Clusters::find()->all();

            foreach ( $clusters as $model )
            {
                $disabled += $this->_checkClusterConvs( $model );
            }
        }

        $elapsed = time() - $start;

        echo 'Check cluster conversions: '.$disabled.' campaigns with delivery frequency set to 0 - Elapsed time: '.$elapsed.' seg.<hr/>';        
    }


    private function _checkClusterConvs ( $model )
    {
        if ( !$model->autostop_limit )
            return 0;

        $campaigns = $this->_redis->zrangebyscore( 
            'clusterimps:'.$model->id,  
            $model->autostop_limit, 
            '+inf'
        );

        foreach ( $campaigns AS $cid )
        {
            $chc = models\ClustersHasCampaigns::findOne( 
                ['Campaigns_id' => $cid, 'Clusters_id' => $model->id] 
            );

            if ( $chc )
            {
                $chc->prev_freq     = $chc->delivery_freq;
                $chc->autostopped   = true;
                $chc->delivery_freq = 0;

                if ( $chc->save() )
                {
                    models\CampaignsChangelog::log( $cid, 'no_conv_limit', null, $model->id );

                    if ( $chc->campaigns->app_id )
                    {
                        $packageIds = json_decode($chc->campaigns->app_id);

                        foreach ( $packageIds as $packageId )
                        {
                            $this->_redis->zadd( 
                                'clusterlist:'.$model->id, 
                                0, 
                                $cid.':'.$chc->campaigns->affiliates->id.':'.$packageId
                            );
                        }
                    }

                    $this->_redis->zrem( 'clusterimps:'.$model->id, $cid );
                }                
               
                unset ($chc);
            }
        }

        return count($campaigns);
    }


    private function _sendmail ( $from, $to, $subject, $body )
    {
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


    public function actionPopulatecache ( )
    {
        $this->actionPopulateclusters();
        $this->actionPopulatecampaigns();
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

            $clustersHasCampaigns = models\ClustersHasCampaigns::findAll( ['Clusters_id' => $model->id] );

            foreach ( $clustersHasCampaigns as $assign )
            {
                if ( $assign->campaigns->status=='active' && isset($assign->campaigns->app_id) && isset($assign->delivery_freq) )
                {
                    $cache = new \Predis\Client( \Yii::$app->params['predisConString'] );

                    $packageIds = json_decode($assign->campaigns->app_id);

                    foreach ( $packageIds as $packageId )
                    {
                        $cache->zadd( 'clusterlist:'.$model->id, $assign->delivery_freq, $assign->campaigns->id.':'.$assign->campaigns->affiliates->id.':'.$packageId );
                    }

                    // set campaign's cap in redis
                    if ( isset($assign->campaigns->daily_cap) )
                    {
                        $cache->zadd( 
                            'clustercaps:'.$model->id, 
                            $assign->campaigns->daily_cap,
                            $assign->campaigns->id
                        ); 
                    }
                    else if ( isset($campaign->aff_daily_cap) )
                    {
                        $cache->zadd( 
                            'clustercaps:'.$model->id, 
                            $assign->campaigns->aff_daily_cap,
                            $assign->campaigns->id
                        );
                    }
                    else
                    {
                        $cache->zrem( 
                            'clustercaps:'.$model->id, 
                            $assign->campaigns->id
                        );                 
                    }                    
                }
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
                'publisher_id'      => $model->Publishers_id,
                'status'            => $model->status,
                'size'              => $model->size,
                'imps'              => $imps,
                'health_check_imps' => $hcimps
            ]);
        }

        $elapsed = time() - $start;

        echo 'Placements cached: '.count($placements).' - Elapsed time: '.$elapsed.' seg.<hr/>';
    }        


    public function actionPopulatecampaigns ( )
    {
        $this->_redis->select( 0 );

        $start = time();

        $campaigns = models\Campaigns::find()->all();

        foreach ( $campaigns as $model )
        {
            $this->_redis->hmset( 'campaign:'.$model->id, [
                'callback'      => $model->landing_url,
                'ext_id'        => $model->ext_id,
                'click_macro'   => $model->affiliates->click_macro,
                'placeholders'  => $model->affiliates->placeholders,
                'macros'        => $model->affiliates->macros
            ]);
        }

        $elapsed = time() - $start;

        echo 'Campaigns cached: '.count($campaigns).' - Elapsed time: '.$elapsed.' seg.<hr/>';
    }


    public function actionDailymaintenance ( )
    {
        $this->_db = 'yesterday';

        $this->actionIndex();

        if ( 
            $this->_redis->zcard( 'loadedlogs') === 0 
            && $this->_redis->zcard( 'loadedclicks') === 0
        )
        {
        }
        else
        {
            
        }
    }


    public function actionRedshift ( )
    {
        $db = new \PDO( 
            'pgsql:dbname=dinky;host=dinky.cspssu6efoeo.us-east-1.redshift.amazonaws.com;port=5439',
            'root',
            'spl4dPr0j3ct'
        );


    }

    public function actionStorelogs ( $date_start = null, $date_end = null, $move = false )
    {
        $start = time();

        ini_set('memory_limit','3000M');
        set_time_limit(0);        


        if ( $date_start )
        {
            $tableName  = date('y_m', strtotime($date_start));
            $date_start = '"'.$date_start.'"';

            if ( $date_end )
            {
                $tableName2 = date('y_m', strtotime($date_end));
                $date_end   = '"'.$date_end.'"';
            }
            else
            {
                $tableName2 = date('y_m');
                $date_end = 'CURDATE() - INTERVAL 1 DAY';
            }

            if ( $tableName != $tableName2 )
                die('Date range must be within the same month');
        }
        else
        {
            $tableName  = date('y_m');
            $date_start = 'CURDATE() - INTERVAL 1 DAY';
            $date_end   = 'CURDATE() - INTERVAL 1 DAY';
        }


        $sql = '
            INSERT INTO F_ClusterLogs_'.$tableName.' (
                session_hash,
                D_Placement_id,
                D_Campaign_id,
                cluster_id,
                cluster_name,
                imps,
                imp_time,
                clicks,
                country,
                connection_type,
                carrier,
                device,
                device_model,
                device_brand,
                os,
                os_version,
                browser,
                browser_version,
                cost,
                imp_status,               
                pub_id,   
                subpub_id, 
                device_id,                                                             
                exchange_id
            )
            SELECT
                session_hash,
                D_Placement_id,
                D_Campaign_id,
                cluster_id,
                cluster_name,
                imps,
                imp_time,
                clicks,
                country,
                connection_type,
                carrier,
                device,
                device_model,
                device_brand,
                os,
                os_version,
                browser,
                browser_version,
                cost,
                imp_status,                
                pub_id,
                subpub_id,  
                device_id,              
                exchange_id

            FROM F_ClusterLogs

            WHERE DATE(imp_time) BETWEEN '.$date_start.' AND '.$date_end.' 
            
            ON DUPLICATE KEY UPDATE cost=VALUES(cost), imps=VALUES(imps);
        ';

        $clusterLogs = \Yii::$app->db->createCommand( $sql )->execute();

        if ( $move && $clusterLogs )
        {
            $sql = 'DELETE FROM F_ClusterLogs WHERE DATE(imp_time) BETWEEN '.$date_start.' AND '.$date_end.';';
            
            \Yii::$app->db->createCommand( $sql )->execute();
        }

        $clusterLogsElapsed = time() - $start;

        $start = time();

        $sql = '
            INSERT INTO F_CampaignLogs_'.$tableName.' (
                click_id,
                D_Campaign_id,
                D_Placement_id,
                session_hash,
                click_time,
                conv_time,
                revenue
            )
            SELECT
                click_id,
                D_Campaign_id,
                D_Placement_id,
                session_hash,
                click_time,
                conv_time,
                revenue

            FROM F_CampaignLogs

            WHERE DATE(IF(conv_time is not null, conv_time, click_time)) BETWEEN '.$date_start.' AND '.$date_end.' 
            ON DUPLICATE KEY UPDATE click_time=VALUES(click_time);
        ';

        $campaignLogs = \Yii::$app->db->createCommand( $sql )->execute();

        if ( $move && $campaignLogs )
        {
            $sql = 'DELETE FROM F_CampaignLogs WHERE DATE(IF(conv_time is not null, conv_time, click_time)) BETWEEN '.$date_start.' AND '.$date_end.';';

            \Yii::$app->db->createCommand( $sql )->execute();
        }

        $campaignLogsElapsed = time() - $start;

        echo 'Cluster Logs Stored: '.count($clusterLogs).' - Elapsed time: '.$clusterLogsElapsed.' seg.<hr/>';        
        echo 'Campaign Logs Stored: '.count($campaignLogs).' - Elapsed time: '.$campaignLogsElapsed.' seg.<hr/>';                
    }


    public function actionToredshift ( $date_start = null, $date_end = null, $queries = 1500 )
    {
        $db = new \PDO( 
            'pgsql:dbname=prod;host=dinky.cspssu6efoeo.us-east-1.redshift.amazonaws.com;port=5439',
            'root',
            'spl4dPr0j3ct'
        );

        ini_set('memory_limit','3000M');
        set_time_limit(0);        

        if ( $date_start )
        {
            $tableName  = date('y_m', strtotime($date_start));
            $date_start = '"'.$date_start.'"';

            if ( $date_end )
            {
                $tableName2 = date('y_m', strtotime($date_end));
                $date_end   = '"'.$date_end.'"';
            }
            else
            {
                $tableName2 = date('y_m');
                $date_end = 'CURDATE() - INTERVAL 1 DAY';
            }

            if ( $tableName != $tableName2 )
                die('Date range must be within the same month');
        }
        else
        {
            $tableName  = date('y_m');
            $date_start = 'CURDATE() - INTERVAL 1 DAY';
            $date_end   = 'CURDATE() - INTERVAL 1 DAY';
        }

        $this->_clusterLogsToRedshift ( $db, $date_start, $date_end, $tableName, $queries );
        $this->_campaignLogsToRedshift ( $db, $date_start, $date_end, $tableName, $queries );
    }   


    private function _clusterLogsToRedshift ( $db, $date_start, $date_end, $tableName, $queries )
    {
        $start      = time();
        $results    = 1;
        $rows       = 0;
        $start_at   = 0;
        $queryCount = 0;

        while ( $results>0 && $queryCount<$queries )
        {
            $results = $this->_clusterLogsToRedshiftQuery(
                $start_at,
                $db,
                $date_start,
                $date_end,
                $tableName
            );

            $start_at += $this->_objectLimit;              
            $rows     += $results;
            $queryCount++;

        }

        $clusterLogsElapsed = time() - $start;

        echo 'Cluster Logs Stored: '.count($rows).' - Elapsed time: '.$clusterLogsElapsed.' seg.<hr/>';        
    }


    private function _clusterLogsToRedshiftQuery ( $start_at, $db, $date_start, $date_end, $tableName )
    {
        $select = '
            SELECT *   

            FROM F_ClusterLogs_'.$tableName.'

            WHERE DATE(imp_time) BETWEEN '.$date_start.' AND '.$date_end.' AND loaded<>1  
        ';

        $update = '
            UPDATE F_ClusterLogs_'.$tableName.' SET loaded=1 WHERE session_hash IN 
        ';        

        $hashesForUpdate = '';

        $q = $select . ' LIMIT ' . $start_at . ',' . $this->_objectLimit . ';';

        $values = '';

        $clusterLogs = \Yii::$app->db->createCommand( $q )->queryAll();


        if ( $clusterLogs )
        {
            foreach ( $clusterLogs as $row )
            {
                if ( $hashesForUpdate != '' )
                    $hashesForUpdate .= ',';

                $hashesForUpdate .= '"'.$row['session_hash'].'"';

                if ( !$row['D_Placement_id'] || $row['D_Placement_id']=='' || !preg_match( '/^[0-9]+$/',$row['D_Placement_id'] ) )
                    $row['D_Placement_id'] = "NULL";

                if ( !$row['D_Campaign_id'] || $row['D_Campaign_id']=='' || !preg_match( '/^[0-9]+$/',$row['D_Campaign_id'] ) )
                    $row['D_Campaign_id'] = "NULL";                    

                if ( $row['pub_id'] && $row['pub_id']!='' )
                    $row['pub_id'] = "'".$this->_escapePostgreSql( $row['pub_id'] )."'";
                else
                    $row['pub_id'] = "NULL";


                if ( $row['subpub_id'] && $row['subpub_id']!='' )
                    $row['subpub_id'] = "'".$this->_escapePostgreSql( $row['subpub_id'] )."'";
                else
                    $row['subpub_id'] = "NULL";


                if ( $row['exchange_id'] && $row['exchange_id']!='' )
                    $row['exchange_id'] = "'".$this->_escapePostgreSql( $row['exchange_id'] )."'";
                else
                    $row['exchange_id'] = "NULL";


                if ( $row['country'] && $row['country']!='' )
                    $row['country'] = "'".strtoupper($row['country'])."'";
                else
                    $row['country'] = "NULL";


                if ( $row['carrier'] && $row['carrier']!='' )
                    $row['carrier'] = "'".$this->_escapePostgreSql( $row['carrier'] )."'";
                else
                    $row['carrier'] = "NULL";


                if ( $row['connection_type'] && $row['connection_type']!='' )
                {
                    if ( $row['connection_type']== '3g' || $row['connection_type']== '3G' )
                        $row['connection_type']= "'MOBILE'";

                    $row['connection_type'] = "'".strtoupper($row['connection_type'])."'";
                }
                else
                    $row['connection_type'] = "NULL";


                if ( isset($row['idfa']) && $row['idfa'] && $row['idfa']!='' )
                    $deviceId = "'".$this->_escapePostgreSql( $row['idfa'] )."'";
                else if ( isset($row['gaid']) && $row['gaid'] && $row['gaid']!='' )
                    $deviceId = "'".$this->_escapePostgreSql( $row['gaid'] )."'";
                else if ( $row['device_id'] && $row['device_id']!='' )
                    $deviceId = "'".$this->_escapePostgreSql( $row['device_id'] )."'";                    
                else
                    $deviceId = "NULL";


                if ( !isset($row['device']) || !$row['device'] || $row['device']=='' )
                    $row['device'] = 'NULL';
                else
                    $row['device'] = "'".ucwords(strtolower($row['device']))."'";


                if ( isset($row['device_brand']) && $row['device_brand'] && $row['device_brand']!='' )
                    $row['device_brand'] = "'".$this->_escapePostgreSql( $row['device_brand'] )."'";
                else
                    $row['device_brand'] = "NULL";


                if ( isset($row['device_model']) && $row['device_model'] && $row['device_model']!='' )
                    $row['device_model'] = "'".$this->_escapePostgreSql( $row['device_model'] )."'";
                else
                    $row['device_model'] = "NULL";


                if ( isset($row['os']) && $row['os'] && $row['os']!='' )
                    $row['os'] = "'".$this->_escapePostgreSql( $row['os'] )."'";
                else
                    $row['os'] = "NULL";


                if ( isset($row['os_version']) && $row['os_version'] && $row['os_version']!='' )
                    $row['os_version'] = "'".$this->_escapePostgreSql( $row['os_version'] )."'";
                else
                    $row['os_version'] = "NULL";   


                if ( isset($row['browser']) && $row['browser'] && $row['browser']!='' )
                    $row['browser'] = "'".$this->_escapePostgreSql( $row['browser'] )."'";
                else
                    $row['browser'] = "NULL";  

                if ( isset($row['browser_version']) && $row['browser_version'] && $row['browser_version']!='' )
                    $row['browser_version'] = "'".$this->_escapePostgreSql( $row['browser_version'] )."'";
                else
                    $row['browser_version'] = "NULL";


                if ( $row['device']=='Phablet' || $row['device']=='Smartphone' )
                    $row['device'] = "'mobile'";

                if ( isset( $row['imp_status'] ) && $row['imp_status'] && $row['imp_status']!='' )
                    $impStatus = "'".$row['imp_status']."'";
                else
                    $impStatus = "NULL";               

                if ( isset($row['clicks']) && $row['clicks'] )
                    $clicks = $row['clicks'];
                else
                    $clicks = 0; 


                if ( $values != '' )
                    $values .= ',';

                $values .= "
                    (
                    '".$row['session_hash']."',
                    ".$row['D_Placement_id'].",
                    ".$row['D_Campaign_id'].",
                    ".$row['cluster_id'].",
                    '".$row['cluster_name']."',
                    ".$row['imps'].",
                    '".$row['imp_time']."',
                    ".$clicks.",
                    ".$row['country'].",
                    ".$row['connection_type'].",
                    ".$row['carrier'].",
                    ".$row['device'].",
                    ".$row['device_model'].",
                    ".$row['device_brand'].",
                    ".$row['os'].",
                    ".$row['os_version'].",
                    ".$row['browser'].",
                    ".$row['browser_version'].",
                    ".$row['cost'].",
                    ".$row['exchange_id'].",
                    ".$deviceId.",
                    ".$impStatus.",
                    ".$row['pub_id'].",                            
                    ".$row['subpub_id']."
                    )                    
                ";
            }

            $insert = '
                INSERT INTO f_clusterlogs_'.$tableName.' (
                    session_hash,
                    D_Placement_id,
                    D_Campaign_id,
                    cluster_id,
                    cluster_name,
                    imps,
                    imp_time,
                    clicks,
                    country,
                    connection_type,
                    carrier,
                    device,
                    device_model,
                    device_brand,
                    os,
                    os_version,
                    browser,
                    browser_version,
                    cost,
                    exchange_id,
                    device_id,
                    imp_status,
                    pub_id,
                    subpub_id
                )
                VALUES '.$values.'
            ';

            $statement = $db->prepare( $insert );

            if ( !$statement->execute() )
            {
                var_dump($statement->errorInfo());

                echo '<hr>'. $insert;

                die();
            }

            $u = $update . '(' . $hashesForUpdate . ');';

            \Yii::$app->db->createCommand( $u )->execute();

            return $statement->rowCount(); 
        }
        else
        {
            return 0;
        }        
    }


    private function _campaignLogsToRedshift ( $db, $date_start, $date_end, $tableName, $queries )
    {
        $start = time();

        $results    = 1;
        $start_at   = 0;
        $rows       = 0;
        $queryCount = 0;

        while ( $results>0 && $queryCount<$queries )
        {
            $results = $this->_campaignLogsToRedshiftQuery(
                $start_at,
                $db,
                $date_start,
                $date_end,
                $tableName
            );

            $start_at += $this->_objectLimit;              
            $rows     += $results;
            $queryCount++;
        }


        $campaignLogsElapsed = time() - $start;  

        echo 'Campaign Logs Stored:'.count($rows).' - Elapsed time: '.$campaignLogsElapsed.' seg.<hr/>';              
    } 


    private function  _campaignLogsToRedshiftQuery ( $start_at, $db, $date_start, $date_end, $tableName  )
    {
        $select = '
            SELECT *   

            FROM F_CampaignLogs_'.$tableName.'

            WHERE DATE(IF(conv_time is not null, conv_time, click_time)) BETWEEN '.$date_start.' AND '.$date_end.' AND loaded<>1
        ';
                    
        $q = $select . ' LIMIT ' . $start_at . ',' . $this->_objectLimit . ';';
        $values = '';

        $update = '
            UPDATE F_CampaignLogs_'.$tableName.' SET loaded=1 WHERE click_id IN 
        ';        

        $clicksForUpdate = '';        

        $campaignLogs = \Yii::$app->db->createCommand( $q )->queryAll();

        if ( $campaignLogs )
        {
            foreach ( $campaignLogs as $row )
            {
                if ( $values != '' )
                    $values .= ',';

                if ( $clicksForUpdate != '' )
                    $clicksForUpdate .= ',';

                $clicksForUpdate .= '"'.$row['click_id'].'"';

                if ( !$row['click_time'] || $row['click_time']=='' )
                    $clickTime = "NULL";
                else
                    $clickTime = $row['click_time'];

                $values .= "
                    (
                    '".$row['click_id']."',    
                    ".$row['D_Campaign_id'].",                        
                    '".$row['session_hash']."',
                    '".$clickTime."'
                    )                    
                ";
            }

            $insert = '
                INSERT INTO f_campaignlogs_'.$tableName.' (
                    click_id,
                    D_Campaign_id,
                    session_hash,
                    click_time
                )
                VALUES '.$values.'
            ';

            $statement = $db->prepare( $insert );

            if ( !$statement->execute() )
            {
                var_dump($statement->errorInfo());

                echo '<hr>'. $insert;

                die();
            }

            $u = $update . '(' . $clicksForUpdate . ');';

            \Yii::$app->db->createCommand( $u )->execute();

            return $statement->rowCount();
        }
        else
        {
            return 0;
        }        
    }


    public function actionClickcount ( $campaign_id, $date = null, $loaded = true )
    {
        if ( !$campaign_id )
            die('Please enter a valid Campaign ID');

        $start           = time();

        switch ( $this->_db )
        {
            case 'yesterday':
                $this->_redis->select( $this->_getYesterdayDatabase() );
            break;
            case 'current':
                $this->_redis->select( $this->_getCurrentDatabase() );
            break;
        }    

        if ( $date )
            $tstamp = strtotime( $date );
        else
            $tstamp = strtotime( date('Y-m-d') );

        if ( $loaded )
            $index = 'loadedclicks';
        else
            $index = 'clickids';

        $clusterLogCount = $this->_redis->zcard( $index );
        $queries         = ceil( $clusterLogCount/$this->_objectLimit );
        $clicks          = 0;


        // build separate sql queries based on $_objectLimit in order to control memory usage
        for ( $i=0; $i<$queries; $i++ )
        {
            $clickIDs = $this->_redis->zrangebyscore( 'clickids', 0, $this->_objectLimit, $tstamp, $tstamp+86400 );

            if ( $clickIDs )
            {
                // add each campaign log to sql query
                foreach ( $clickIDs as $clickID )
                {
                    $campaignLog = $this->_redis->hgetall( 'campaignlog:'.$clickID );

                    if ( 
                        $campaign_id == $campaignLog['campaign_id'] 
                        && $campaignLog['click_time'] >= $tstamp 
                        && $campaignLog['click_time'] <= $tstamp+86400 
                    )
                    {
                        $clicks++;
                    }

                    unset($campaignLog);
                }
            }
        }

        $elapsed = time() - $start;

        echo 'Clicks: '.$clicks.' - elapsed time: '.$elapsed.' seg.<hr/>';
    }

}