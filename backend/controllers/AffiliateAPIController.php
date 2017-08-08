<?php

namespace backend\controllers;

use Predis;

class AffiliateAPIController extends \yii\web\Controller
{

	const NOTIFY_INBOX = '';
	const ALERTS_INBOX = '';

	protected $_notifications;
	protected $_redis;


	protected function _apiRules ( )
	{
		return [
			[
				'class' 		=> 'RegamingAPI',
				'affiliate_id'	=> 1,
			],			
		];
	}


    public function actionIndex()
    {	
    	$this->_notifications = [];
    	$this->_redis = new \Predis\Client( \Yii::$app->params['predisConString'] );

    	foreach ( $this->_enabledAPIs() AS $rule )
    	{
    		$api 		= new $rule['class'];
    		$affiliate  = Affiliates::findByPk( $rule['affiliate_id'] );

    		try
    		{
    			$campaignsData  = $api->request( $affiliate->api_key, $affiliate->user_id );

    			if ( $campaignsData && is_array($campaignsData) )
    			{
    				foreach ( $campaignsData AS $campaignData )
    				{
    					$campaign = Campaigns::find([ 'ext_id' => $campaignData['ext_id'] ]);

    					if  ( $campaign )
    						$this->_checkChanges( $rule['class'], $campaign, $campaignData );
    					else
    						$campaign = new Campaigns;

    					$campaign->name    		= $campaignData['name'];
    					$campaign->status  		= $campaignData['status'];
    					$campaign->ext_id  		= $campaignData['ext_id'];
    					$campaign->payout  		= $campaignData['payout'];
    					$campaign->landing_url  = $campaignData['landing_url'];

    					if ( $campaignData['country'] )
    						$campaign->country  	= json_encode($campaignData['country']);

    					if ( $campaignData['device_type'] )
    						$campaign->device_type  = json_encode($campaignData['device_type']);

    					if ( $campaignData['os'] )
    						$campaign->os 			= json_encode($campaignData['os']);		

    					if ( $campaignData['os_version'] )
    						$campaign->os_version	= json_encode($campaignData['os_version']);		    								

    					if ( $campaignData['carrier'] )
    						$campaign->carrier 		= json_encode($campaignData['carrier']);		    					

    					if ( $campaignData['connection_type'] )
    						$campaign->connection_type = json_encode($campaignData['connection_type']);    					

    					$campaign->save();

    					unset( $campaign );				
    				}
    			}
    			else
    			{
    				$this->_createAlert( $api->getMessages() );
    				continue;
    			}
    		}
    		catch ( Exception $e )
    		{
    			$msg = '';
    			$this->_createAlert( $msg );
    			continue;
    		}

    		unset ( $api );
    		unset ( $affiliate );
    	}

    	$this->_sendAlerts();
    	$this->_sendNotifications();

        return $this->render('index');
    }


    protected function _checkChanges ( $api_class, Campaigns $campaign, array $campaignData )
    {
    	$id 	 = $campaign->id;
    	$changes = [];


    	if ( $campaign->payout != $campaignData['payout'] )
    		$changes[] = 'payout';

		if ( empty( array_diff( json_decode($campaign->carrier), $campaignData['device_type'] ) ) )
    		$changes[] = 'carrier'; 

		if ( empty( array_diff( json_decode($campaign->connection_type), $campaignData['connection_type'] ) ) ) 
    		$changes[] = 'connection type';   

		if ( empty( array_diff( json_decode($campaign->country), $campaignData['country'] ) ) )
    		$changes[] = 'country';	

		if ( empty( array_diff( json_decode($campaign->device_type), $campaignData['device_type'] ) ) ) 
    		$changes[] = 'device';   	

		if ( empty( array_diff( json_decode($campaign->os), $campaignData['os'] ) ) ) 
    		$changes[] = 'os';      	

		if ( empty( array_diff( json_decode($campaign->os_version), $campaignData['os_version'] ) ) ) 
    		$changes[] = 'os_version';   


    	if ( !empty($changes) )
    	{
    		$clusters 			  = [];
			$clustersHasCampaigns = models\ClustersHasCampaigns::findAll( ['Campaigns_id' => $campaign->id] );

			switch ( $campaignData['status'] )
			{
				case 'active':
					$status = 1;
				break;
				default:
					$status = 0;
				break;
			}

			foreach ( $clusterHasCampaigns as $assign )
			{
				$clusters[] = $clusterHasCampaigns['Clusters_id'];

				if ( $campaign->status != $campaignData['status'] )
				{
					$this->_redis->zadd( 'clusterlist:'.$clusterHasCampaigns['Clusters_id'], $status, $campaign->id );
				}
			}

			if ( $campaign->status != $campaignData['status'] )
				$changes[] = 'status';

	    	$this->_notifications[$api_class][$id] = [
	    		'changes' 	=> $changes,
	    		'clusters'	=> $clusters
	    	];  			
    	}
    }



    protected function _createAlert ( $messages )
    {

    }


    protected function _sendAlerts( )
    {

    }


    protected function _sendNotifications( )
    {
    	$content = '';

    	foreach ( $this->_notifications AS $api => $notifications )
    	{
    		foreach ( $notifiactions AS $cid => $data )
    		{
    			$content .= '
                    <td>'.$api.'</td>
                    <td>'.$cid.'</td>
                    <td>'.$cid.'</td>
                    <td>'.implode( ',', $data['changes'] ).'</td>
                   	<td>'.implode( ',', $data['clusters'] ).'</td>
    			';
    		}
    	}

		$html = '
            <html>
                <head>
                </head>
                <body>
                    <table>
                        <thead>
                            <td>API</td>
                            <td>CAMPAIGN ID</td>
                            <td>COUNTRY</td>
                            <td>CARRIER</td>
                            <td>CONNECTION</td>
                            <td>DEVICE</td>
                            <td>OS</td>
                            <td>OS VERSION</td>
                           	<td>AFFECTED CLUSTERS</td>
                        </thead>
                        <tbody>'.$content.'</tbody>
                    </table>
                </body>
            </html>		
		';    	
    }

}
