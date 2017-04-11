<?php

namespace backend\controllers;

use Elasticsearch\ClientBuilder;

class EtlController extends \yii\web\Controller
{

	private $_redis;
	private $_objectLimit;
	private $_lastEtlTime;
	private $_currentEtlTime;
	private $_elasticSearch;
    private $_placementSql;


	public function __construct ( $id, $module, $config = [] )
	{
		parent::__construct( $id, $module, $config );

		// enable elastic search client
		//$this->_elasticSearch = ClientBuilder::create()->setHosts(["localhost:9200"])->setSelector('\Elasticsearch\ConnectionPool\Selectors\StickyRoundRobinSelector')->build();

    	$this->_redis 	 	  	= new \Predis\Client( \Yii::$app->params['predisConString'] );

    	$this->_objectLimit 	= 100000; // how many objects to process at once

    	$lastEtlTime   			= $this->_redis->get( 'last_etl_time');
    	$this->_lastEtlTime 	= $lastEtlTime ?  $lastEtlTime : 0;
    	$this->_currentEtlTime	= time();	

		\ini_set('memory_limit','3000M');
		\set_time_limit(0);
	}


    public function actionIndex( )
    {
		$this->placements();
		$this->campaigns();
		$this->imps();
		//$this->convs();
        //$this->updatePlacements();
		
		$this->_redis->set( 'last_etl_time', $this->_currentEtlTime );
        
        \gc_collect_cycles();
        // return $this->render('index');
    }


    public function updatePlacements ( )
    {
        $start   = time();        
        $rows    = \Yii::$app->db->createCommand( $this->_placementSql )->execute();
        $elapsed = time() - $start;

        echo 'Updated Placements: '.$rows.' rows - load time: '.$elapsed.' seg.<hr/>';
    }


    public function convs ( )
    {
    	$start 		   = time();
    	$convIDCount   = $this->_redis->zcard( 'convs' );
    	$queries 	   = ceil( $convIDCount/$this->_objectLimit );
    	$startAt 	   = 0;
    	$rows   	   = 0;
    	$queries 	   = 0;

		// build separate sql queries based on $_objectLimit in order to control memory usage
    	for ( $i=0; $i<=$queries; $i++ )
    	{
    		// call each query from a separated method in order to force garbage collection (and free memory)
    		$rows    += $this->_buildConvQuery ( $startAt, $startAt+$this->_objectLimit );
    		$startAt += $this->_objectLimit;
    	}

		$elapsed = time() - $start;
		echo 'Conversions: '.$rows.' rows - sql queries: '.$queries.' - load time: '.$elapsed.' seg.<hr/>';
    }


    private function _buildConvQuery ( $start_at,  $end_at )
    {
		$sql 		= '';
		$params 	= [];
		$paramCount = 0;

		$convIDs 	= $this->_redis->zrangebyscore( 'convs', $this->_lastEtlTime, $this->_currentEtlTime, $start_at, $end_at );

		// ad each conversion to SQL query
		foreach ( $convIDs as $clickID )
		{
			$convTime    	= $this->_redis->get( 'conv:'.$clickID );

			// using params because clickID comes from browser
			$param 			= ':i'.$paramCount;
			$params[$param] = $clickID;
			$cost 			= 0;

			$sql .= '
				UPDATE IGNORE F_CampaignLogs cpl 
                    LEFT JOIN F_ClusterLogs cl ON ( cpl.session_hash = cl.session_hash ) 
					LEFT JOIN campaigns c ON ( i.D_Campaign_id = c.id ) 
					LEFT JOIN D_Placement_id ON p ( i.D_Placement_id = p.id ) 
				SET 
					cpl.conv_time="'.\date( 'Y-m-d H:i:s', $convTime ).'", 
					cpl.revenue = c.payout, 
					cl.cost = CASE 
						WHEN p.model = "RS" THEN '.$cost.' END 
				WHERE 
					click_id = '.$param.'
			;';

			$paramCount++;
		}

		if ( $sql != '' )
			return \Yii::$app->db->createCommand( $sql )->bindValues( $params )->execute();
    }


    public function imps ( )
    {
    	$this->campaignLogs();
    	$this->clusterLogs();
    }


    public function campaignLogs ( )
    {
    	$start 			= time();
    	$clickIDCount   = $this->_redis->zcount( 'clickids', $this->_lastEtlTime, $this->_currentEtlTime );
    	$queries 		= ceil( $clickIDCount/$this->_objectLimit );
    	$startAt 		= 0;
    	$rows 			= 0;

    	// echo ('total click IDs: '.$clickIDCount .'<hr/>');

    	// build separate sql queries based on $_objectLimit in order to control memory usage
    	for ( $i=0; $i<$queries; $i++ )
    	{
    		// call each query from a separated method in order to force garbage collection (and free memory)
    		$rows += $this->_buildCampaignLogsQuery( $startAt, $startAt+$this->_objectLimit );
    		$startAt += $this->_objectLimit;
    	}

		$elapsed = time() - $start;
		echo 'CampaignLogs: '.$rows.' rows - sql queries: '.$queries.' - load time: '.$elapsed.' seg.<hr/>';
    }


    private function _buildCampaignLogsQuery ( $start_at, $end_at )
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

        echo 'query => '. $start_at.': '.$end_at.'<br>';
        
    	$clickIDs = $this->_redis->zrangebyscore( 'clickids', $this->_lastEtlTime,$this->_currentEtlTime,  'LIMIT', $start_at, $end_at );

		if ( $clickIDs )
		{
			// create elastic search data
			// $params = ['body' => []];

			// add each campaign log to sql query
    		foreach ( $clickIDs as $clickID )
    		{
    			$campaignLog = $this->_redis->hgetall( 'campaignlog:'.$clickID );

    			if ( $values != '' )
    				$values .= ',';

    			// append to elastic search data
    			/*
				$params['body'][] = [
					'index' => [
						'_index' => 'traffic',
						'_type' => 'CampaignLog',
						'_id' => $clickID
					]
				];				

				$params['body'][] = $campaignLog;
				*/

                if ( $campaignLog['click_time'] )
                    $clickTime = '"'.\date( 'Y-m-d H:i:s', $campaignLog['click_time'] ).'"';
                else
                    $clickTime = 'NULL';

    			$values .= '( 
                    "'.$clickID.'",
    				'.$campaignLog['campaign_id'].',
    				"'.$campaignLog['session_hash'].'",
    				'.$clickTime.'
    			)';

                // free memory because there is no garbage collection until block ends
                unset( $campaignLog );
    		}

    		if ( $values != '' )
    		{
	    		$sql .= $values . ' ON DUPLICATE KEY UPDATE click_time=VALUES(click_time);';

	    		// save on elastic search
	    		// $this->_elasticSearch->bulk($params);
	    		return \Yii::$app->db->createCommand( $sql )->execute();    			
    		}
		}

        unset( $clickIDs );

		return 0;
    }


    public function clusterLogs ( )
    {
    	$start 			   = time();
    	$clusterLogCount   = $this->_redis->zcard( 'sessionhashes' );
    	$queries 		   = ceil( $clusterLogCount/$this->_objectLimit );
    	$startAt 		   = 0;
    	$rows   		   = 0;

    	// build separate sql queries based on $_objectLimit in order to control memory usage
    	for ( $i=0; $i<$queries; $i++ )
    	{
    		// call each query from a separated method in order to force garbage collection (and free memory)
    		$rows += $this->_buildClusterLogsQuery( $startAt, $startAt+$this->_objectLimit );
			$startAt += $this->_objectLimit;
    	}

		$elapsed = time() - $start;

		echo 'ClusterLogs: '.$rows.' rows - sql queries: '.$queries.' - load time: '.$elapsed.' seg.<hr/>';
    }


    private function _buildClusterLogsQuery ( $start_at, $end_at )
    {
    	$sql = '
    		INSERT INTO F_ClusterLogs (
                session_hash,
    			D_Placement_id,
    			cluster_id,
    			imps,
    			imp_time,
    			cost,
    			country,
    			connection_type,
    			carrier,
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

        echo 'query => '. $start_at.': '.$end_at.'<br>';

		$sessionHashes = $this->_redis->zrange( 'sessionhashes', $start_at, $end_at );

		if ( $sessionHashes )
		{
            $placements = [];
            $placementSql = '';

			// add each clusterLog to sql query
    		foreach ( $sessionHashes as $sessionHash )
    		{
    			$clusterLog = $this->_redis->hgetall( 'clusterlog:'.$sessionHash );

    			if ( $values != '' )
    				$values .= ',';

    			$values .= '( 
                    "'.$sessionHash.'",
    				'.$clusterLog['placement_id'].',
    				'.$clusterLog['cluster_id'].',
    				'.$clusterLog['imps'].',
    				"'.\date( 'Y-m-d H:i:s', $clusterLog['imp_time'] ).'",
    				'.$clusterLog['cost'].',
    				"'.$clusterLog['country'].'",
    				"'.$clusterLog['connection_type'].'",
    				"'.$clusterLog['carrier'].'",
    				"'.$clusterLog['device'].'",
    				"'.$clusterLog['device_model'].'",
    				"'.$clusterLog['device_brand'].'",
    				"'.$clusterLog['os'].'",
    				"'.$clusterLog['os_version'].'",
    				"'.$clusterLog['browser'].'",
    				"'.$clusterLog['browser_version'].'"
    			)';

                if ( !array_search( $clusterLog['placement_id'], $placements ) )
                {
                    $placements[]      = $clusterLog['placement_id'];
                    $health_check_imps = $this->_redis->hget( 'placement:'.$clusterLog['placement_id'], 'health_check_imps' );
                    $this->_placementSql     .= 'UPDATE placements SET health_check_imps='.$health_check_imps;
                }

                // free memory because there is no garbage collection until block ends
                unset ( $clusterLog );
    		}

    		if ( $values != '' )
    		{
	    		$sql .= $values . ' ON DUPLICATE KEY UPDATE cost=VALUES(cost), imps=VALUES(imps);';

	    		return \Yii::$app->db->createCommand( $sql )->execute();			
    		}
		}

        unset( $sessionHashes );

		return 0;
    }


    public function placements ( )
    {
    	$start = time();

    	$sql = '
    		INSERT IGNORE INTO D_Placement (
    			Publishers_id,
    			name,
    			Publishers_name,
    			model,
    			status
    		)
    		SELECT 
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

		echo 'Placements: '.$rows.' rows inserted - Elapsed time: '.$elapsed.' seg.<hr/>';
    }


    public function campaigns ( )
    {
    	$start = time();

    	$sql = '
    		INSERT IGNORE INTO D_Campaign (
    			Affiliates_id,
    			name,
    			Affiliates_name
    		)
    		SELECT 
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

		echo 'Campaigns: '.$rows.' rows inserted - Elapsed time: '.$elapsed.' seg.<hr/>';
    }        

}