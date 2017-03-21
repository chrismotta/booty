<?php

namespace backend\controllers;

class EtlController extends \yii\web\Controller
{

	private $_redis;
	private $_objectLimit;

    public function actionIndex( )
    {
    	$this->_redis = new \yii\redis\Connection();
    	$this->_redis->database = 0;
    	$this->_redis->hostname = 'localhost';
    	$this->_redis->open();

    	$this->_objectLimit = 900000; // how many objects to get from redis at once

		\ini_set('memory_limit','3000M');
		\set_time_limit(0);

		$this->imps();
		//$this->convs();

        //return $this->render('index');
    }


    public function convs ( )
    {
    	$convIDCount   = $this->_redis->zcard( 'convs' );
    	$convIDQueries = ( $convIDCount/$this->_objectLimit )+1;
    	$startAt = 0;

    	for ( $i=0; $i<=$convIDQueries; $i++ )
    	{
    		unset( $convIDs );

			$sql = '';
			$params = [];
			$paramCount = 0;    		

    		$convIDs = $this->_redis->zrange( 'convs', $startAt, $this->_objectLimit );
    		
    		foreach ( $convIDs as $clickID )
    		{
    			$convTime = $this->_redis->get( 'conv:'.$clickID );

    			// using params because clickID comes from browser
    			$idParam = ':i'.$paramCount;
    			$params[$idParam] = $clickID; 

				$sql .= 'UPDATE INTO F_Imp SET conv_time="'.\date( 'Y-m-d H:i:s', $convTime ).'" WHERE click_id='.$idParam.';';
    		}

    		\Yii::$app->db->createCommand( $sql )->bindValues( $params )->execute();

    		$startAt += $this->_objectLimit;
    	}
    }


    public function imps()
    {
    	$this->_loadCampaignLogs();
    	//$this->_loadClusterLogs();
    }    


    private function _loadCampaignLogs ( )
    {
    	$clickIDCount   = $this->_redis->zcard( 'clickids' );
    	$clickIDQueries = ceil( $clickIDCount/$this->_objectLimit );
    	$startAt = 0;

    	// build separate sql queries based on object limit
    	for ( $i=0; $i<$clickIDQueries; $i++ )
    	{
	    	$sql = '
	    		INSERT INTO F_Imp (
	    			D_Placement_id,
	    			D_Campaign_id,
	    			cluster_id,
	    			session_hash,
	    			imps,
	    			imp_time,
	    			cost,
	    			click_id,
	    			click_time,
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

    		$clickIDs = $this->_redis->zrange( 'clickids', $startAt, $this->_objectLimit );

    		if ( $clickIDs )
    		{
	    		foreach ( $clickIDs as $clickID )
	    		{
	    			$campaignLog = $this->_redis->hgetall( 'campaignlog:'.$clickID );

	    			$clusterLog  = $this->_redis->hgetall( 'clusterlog:'.$campaignLog[1] );

	    			if ( $values != '' )
	    				$values .= ',';

	    			if ( $campaignLog[5] )
	    				$clickTime = \date( 'Y-m-d H:i:s', $campaignLog[5] );
	    			else
	    				$clickTime = \date( 'Y-m-d H:i:s' );

	    			$values .= '( 
	    				'.$clusterLog[3].',
	    				'.$campaignLog[3].',
	    				'.$clusterLog[1].',
	    				"'.$campaignLog[1].'",
	    				'.$clusterLog[29].',
	    				"'.\date( 'Y-m-d H:i:s', $clusterLog[5] ).'",
	    				'.$clusterLog[33].',
	    				"'.$clickID.'",
	    				"'.$clickTime.'",
	    				"'.$clusterLog[9].'",
	    				"'.$clusterLog[11].'",
	    				"'.$clusterLog[13].'",
	    				"'.$clusterLog[19].'",
	    				"'.$clusterLog[21].'",
	    				"'.$clusterLog[23].'",
	    				"'.$clusterLog[15].'",
	    				"'.$clusterLog[17].'",
	    				"'.$clusterLog[25].'",
	    				"'.$clusterLog[27].'"
	    			)';
	    		}

	    		$sql .= $values . ' ON DUPLICATE KEY UPDATE cost=VALUES(cost), imps=VALUES(imps);';

	    		\Yii::$app->db->createCommand( $sql )->execute();

	    		$startAt += $this->_objectLimit;
    		}

    	}
    }


    private function _loadClusterLogs ( )
    {
    	$clusterLogCount   = $this->_redis->zcard( 'clusterlogs' );
    	$clusterLogQueries = ceil( $clusterLogCount/$this->_objectLimit );
    	$startAt = 0;

    	// build separate sql queries based on object limit
    	for ( $i=0; $i<$clusterLogQueries; $i++ )
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

    		$sessionHashes = $this->_redis->zrange( 'clusterlogs', $startAt, $this->_objectLimit );

    		if ( $sessionHashes )
    		{
	    		foreach ( $sessionHashes as $sessionHash )
	    		{    			
	    			$clusterLog = $this->_redis->hgetall( 'clusterlog:'.$sessionHash );

	    			if ( $values != '' )
	    				$values .= ',';

	    			$values .= '( 
	    				'.$clusterLog[3].',
	    				'.$clusterLog[1].',
	    				"'.$sessionHash.'",
	    				'.$clusterLog[29].',
	    				"'.\date( 'Y-m-d H:i:s', $clusterLog[5] ).'",
	    				'.$clusterLog[33].',
	    				"'.$clusterLog[9].'",
	    				"'.$clusterLog[11].'",
	    				"'.$clusterLog[13].'",
	    				"'.$clusterLog[19].'",
	    				"'.$clusterLog[21].'",
	    				"'.$clusterLog[23].'",
	    				"'.$clusterLog[15].'",
	    				"'.$clusterLog[17].'",
	    				"'.$clusterLog[25].'",
	    				"'.$clusterLog[27].'"
	    			)';
	    		}

	    		$sql .= $values . ' ON DUPLICATE KEY UPDATE cost=VALUES(cost), imps=VALUES(imps);';

	    		\Yii::$app->db->createCommand( $sql )->execute();

	    		$startAt += $this->_objectLimit;    			
    		}
    	}    	

    }

}