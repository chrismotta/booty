<?php

namespace backend\controllers;

use Predis;

class AffiliateAPIController extends \yii\web\Controller
{

	const NOTIFY_INBOX = '';
	const ALERTS_INBOX = '';

	protected $_notifications = [];
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
    					$campaign->countries  	= $campaignData['countries'];
    					$campaign->device_types = $campaignData['device_types'];

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


		$countryList = json_encode($campaign->countries);
		if ( empty( array_diff( $countryList, $campaignData['countries'] ) ) )
    		$changes[] = 'countries';	


		$deviceList  = json_encode($campaign->device_types);
		if ( empty( array_diff( $deviceList, $campaignData['device_types'] ) ) ) 
    		$changes[] = 'devices';   	


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
                            <td>CHANGES</td>
                           	<td>AFFECTED CLUSTERS</td>
                        </thead>
                        <tbody>'.$content.'</tbody>
                    </table>
                </body>
            </html>		
		';    	
    }

}
