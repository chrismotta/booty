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


	public function __construct ( $id, $module, $config = [] )
	{
		parent::__construct( $id, $module, $config );

		// enable elastic search client
		//$this->_elasticSearch = ClientBuilder::create()->setHosts(["localhost:9200"])->setSelector('\Elasticsearch\ConnectionPool\Selectors\StickyRoundRobinSelector')->build();

    	$this->_redis 	 	  	= new \Predis\Client( \Yii::$app->params['predisConString'] );

    	$this->_objectLimit 	= 50000; // how many objects to process at once

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

		\gc_collect_cycles();
		$this->_redis->set( 'last_etl_time', $this->_currentEtlTime );

        // return $this->render('index');
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
    		$queries++;
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
				UPDATE IGNORE F_Imp i 
					LEFT JOIN campaigns c ON ( i.D_Campaign_id = c.id ) 
					LEFT JOIN D_Placement_id ON p ( i.D_Placement_id = p.id ) 
				SET 
					i.conv_time="'.\date( 'Y-m-d H:i:s', $convTime ).'", 
					i.revenue = c.payout, 
					i.cost = CASE 
						WHEN p.model = "RS" THEN '.$cost.' END 
				WHERE 
					click_id = '.$param.'
			;';

			$paramCount++;
		}

		if ( $sql != '' )
			return \Yii::$app->db->createCommand( $sql )->bindValues( $params )->execute();
    }


    public function imps()
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
    		$rows = $this->_buildCampaignLogsQuery( $startAt, $startAt+$this->_objectLimit );
    		\gc_collect_cycles();

    		$startAt += $this->_objectLimit;
    		$queries++;
    	}

		$elapsed = time() - $start;
		echo 'CampaignLogs: '.$rows.' rows - sql queries: '.$queries.' - load time: '.$elapsed.' seg.<hr/>';
    }


    private function _buildCampaignLogsQuery ( $start_at, $end_at )
    {
    	$sql = '
    		INSERT INTO F_Imp (
    			D_Campaign_id,
    			click_id,
    			click_time
    		)
			VALUES  
    	';

    	$values = '';

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
    				$clickTime = \date( 'Y-m-d H:i:s', $campaignLog['click_time'] );
    			else
    				$clickTime = \date( 'Y-m-d H:i:s' );

    			$values .= '( 
    				'.$clusterLog['placement_id'].',
    				'.$campaignLog['campaign_id'].',
    				'.$clusterLog['cluster_id'].',
    				"'.$campaignLog['session_hash'].'",
    				'.$clusterLog['imps'].',
    				"'.\date( 'Y-m-d H:i:s', $clusterLog['imp_time'] ).'",
    				'.$clusterLog['cost'].',
    				"'.$clickID.'",
    				"'.$clickTime.'",
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

                unset( $campaignLog );
    		}

    		if ( $values != '' )
    		{
	    		$sql .= $values . ' ON DUPLICATE KEY UPDATE cost=VALUES(cost), imps=VALUES(imps);';

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
    	$clusterLogCount   = $this->_redis->zcard( 'session_hashes' );
    	$queries 		   = ceil( $clusterLogCount/$this->_objectLimit );
    	$startAt 		   = 0;
    	$rows   		   = 0;

    	// build separate sql queries based on $_objectLimit in order to control memory usage
    	for ( $i=0; $i<$queries; $i++ )
    	{
    		// call each query from a separated method in order to force garbage collection (and free memory)
    		$rows += $this->_buildClusterLogsQuery( $startAt, $startAt+$this->_objectLimit );

			$startAt += $this->_objectLimit;
    		$queries++;
    	}

		$elapsed = time() - $start;

		echo 'clusterlog startat: '.$startAt . '<hr/>';
		echo 'ClusterLogs: '.$rows.' rows - sql queries: '.$queries.' - load time: '.$elapsed.' seg.<hr/>';
    }


    private function _buildClusterLogsQuery ( $start_at, $end_at )
    {
    	$sql = '
    		INSERT IGNORE INTO F_Imp (
    			D_Placement_id,
    			cluster_id,
    			session_hash,
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

		$sessionHashes = $this->_redis->zrange( 'clusterlogs', $start_at, $end_at );

		if ( $sessionHashes )
		{
			// add each clusterLog to sql query
    		foreach ( $sessionHashes as $sessionHash )
    		{
    			$clusterLog = $this->_redis->hgetall( 'clusterlog:'.$sessionHash );

    			if ( $values != '' )
    				$values .= ',';

    			$values .= '( 
    				'.$clusterLog['placement_id'].',
    				'.$clusterLog['cluster_id'].',
    				"'.$sessionHash.'",
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