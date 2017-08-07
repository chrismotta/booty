<?php

namespace backend\controllers;

class AffiliateAPIController extends \yii\web\Controller
{

	protected $_notifications = [];


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
    						$this->_checkChanges( $campaign, $campaignData );
    					else
    						$campaign = new Campaigns;

    					$campaign->name    		= $campaignData['name'];
    					$campaign->ext_id  		= $campaignData['ext_id'];
    					$campaign->payout  		= $campaignData['payout'];
    					$campaign->landing_url  = $campaignData['landing_url'];
    					$campaign->countries  	= $campaignData['countries'];
    					$campaign->device_types = $campaignData['device_types'];

    					$campaign->save();   					
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


    protected function _checkChanges ( Campaigns $campaign, array $campaignData )
    {
    	$id = $campaign->id;

    	$this->_notifications[$id] = [
    		'payout' 	=> false,
    		'country'	=> false,
    		'carrier'	=> false,
    		'device'	=> false,
    		'clusters'	=> false
    	];


    	if ( $campaign->payout != $campaignData['payout'] )
    		$this->_notifications[$id]['payout'] = true;


		$countryList = json_encode($campaign->countries);
		if ( empty( array_diff( $countryList['countries'], $campaignData['countries'] ) ) )
    		$this->_notifications[$id]['country'] = true;    	


		$deviceList = json_encode($campaign->device_types);
		if ( empty( array_diff( $deviceList['devices_types'], $campaignData['device_types'] ) ) )			
    		$this->_notifications[$id]['device'] = true;    	


    	if ( isset($this->_notifications[$id]) && !empty($this->_notifications[$id]) )
    	{
			$clustersHasCampaigns = models\ClustersHasCampaigns::findAll( ['Campaigns_id' => $campaign->id] );

			foreach ( $clusterHasCampaigns as $assign )
			{
				$this->_notifications[$id]['clusters'][] = $clusterHasCampaigns['Clusters_id'];
			}
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

    	foreach ( $this->_notifications AS $id => $data )
    	{

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
                            <td>PAYOUT CHANGE</td>
                            <td>COUNTRY CHANGE</td>
                            <td>DEVICE CHANGE</td>
                            <td>CARRIER CHANGE</td>
                            <td>CONN. TYPE CHANGE</td>
                        </thead>
                        <tbody>'.$content.'</tbody>
                    </table>
                </body>
            </html>		
		';    	
    }    

}
