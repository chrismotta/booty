<?php

namespace backend\controllers;

use Yii;
use app\models;
use Predis;
use backend\components;

class AffiliatesapiController extends \yii\web\Controller
{
    CONST FROM = 'Splad - API Controller<no-reply@spladx.co>';
	const NOTIFY_INBOX = 'dev@splad.co,apastor@splad.co,tgonzalez@splad.co,mghio@splad.co,proman@splad.co';
	const ALERTS_INBOX = 'dev@splad.co,apastor@splad.co';

	protected $_notifications;
	protected $_redis;
	protected $_changes;
	protected $_alerts;
	protected $_changed;


	protected function _apiRules ( )
	{
		return [
			[
				'class' 		=> 'RegamingAPI',
				'affiliate_id'	=> 2,
			],	
			[
				'class' 		=> 'SlaviaMobileAPI',
				'affiliate_id'	=> 3,
			],		
            [
                'class'         => 'MobobeatAPI',
                'affiliate_id'  => 5,
            ], 
            [
                'class'         => 'ClicksmobAPI',
                'affiliate_id'  => 6,
            ], 
            [
                'class'         => 'GlispaAPI',
                'affiliate_id'  => 7,
            ],
            [
                'class'         => 'iWoopAPI',
                'affiliate_id'  => 8,
            ],          
            [
                'class'         => 'AppclientsAPI',
                'affiliate_id'  => 9,
            ],
            [
                'class'         => 'MinimobAPI',
                'affiliate_id'  => 10,
            ],
            [
                'class'         => 'AddictiveAdsAPI',
                'affiliate_id'  => 11,
            ],

		];
	}


    public function actionIndex( $affiliate_id = null )
    {
        set_time_limit(0);

    	$this->_changes = '';
    	$this->_alerts  = '';
    	$this->_redis 	= new \Predis\Client( \Yii::$app->params['predisConString'] );


        foreach ( $this->_apiRules() AS $rule )
        {
            if ( !$affiliate_id )
            {
                $this->_runAPI( $rule );
            }            
            if ( $affiliate_id == $rule['affiliate_id'] )
            {                
                $this->_runAPI( $rule );
                break;
            }
        }

        echo '
            <html>
                <head>
                    <style>
                        td {
                            padding:10px;
                            border:1px solid;
                        }
                        table{
                            border:1px solid;
                            border-collapse:collapse;
                        }
                    </style>
                </head>
                <body>
                    <h1>Errors</h1>
                    <table>
                        <thead>
                            <td>API</td>
                            <td>HTTP STATUS</td>                                
                            <td>MESSAGE</td>
                            <td>PARAMS</td>
                        </thead>
                        <tbody>'.$this->_sendAlerts().'</tbody>
                    </table>   
                    <br>
                    <hr>
                    <h1>Notifications</h1>
                    <table>
                        <thead>
                            <td>API</td>
                            <td>CAMPAIGN ID</td>
                            <td>EXT ID</td>
                            <td>PAYOUT</td>
                            <td>COUNTRY</td>
                            <td>CARRIER</td>
                            <td>CONNECTION</td>
                            <td>DEVICE</td>
                            <td>OS</td>
                            <td>OS VERSION</td>
                            <td>STATUS</td>
                            <td>AFFECTED CLUSTERS</td>
                        </thead>
                        <tbody>'.$this->_sendNotifications().'</tbody>
                    </table>                    
                </body>
            </html>         
        ';
    }


    public function actionPocketmedia( )
    {
        set_time_limit(0);

        $this->_changes = '';
        $this->_alerts  = '';
        $this->_redis   = new \Predis\Client( \Yii::$app->params['predisConString'] );

        $this->_runAPI(
            [
                'class'         => 'PocketMediaAPI',
                'affiliate_id'  => 4,
            ]              
        );


        echo '
            <html>
                <head>
                    <style>
                        td {
                            padding:10px;
                            border:1px solid;
                        }
                        table{
                            border:1px solid;
                            border-collapse:collapse;
                        }
                    </style>
                </head>
                <body>
                    <h1>Errors</h1>
                    <table>
                        <thead>
                            <td>API</td>
                            <td>HTTP STATUS</td>                                
                            <td>MESSAGE</td>
                            <td>PARAMS</td>
                        </thead>
                        <tbody>'.$this->_sendAlerts().'</tbody>
                    </table>   
                    <br>
                    <hr>
                    <h1>Notifications</h1>
                    <table>
                        <thead>
                            <td>API</td>
                            <td>CAMPAIGN ID</td>
                            <td>EXT ID</td>
                            <td>PAYOUT</td>
                            <td>COUNTRY</td>
                            <td>CARRIER</td>
                            <td>CONNECTION</td>
                            <td>DEVICE</td>
                            <td>OS</td>
                            <td>OS VERSION</td>
                            <td>STATUS</td>
                            <td>AFFECTED CLUSTERS</td>
                        </thead>
                        <tbody>'.$this->_sendNotifications().'</tbody>
                    </table>                    
                </body>
            </html>         
        ';        
    }


    protected function _runAPI ( array $rule )
    {
        $className  = 'backend\components\\'.$rule['class'];
        $api        = new $className;
        $affiliate  = models\Affiliates::findOne( ['id' => $rule['affiliate_id'] ] );

        try
        {
            $campaignsData  = $api->requestCampaigns( $affiliate->api_key, $affiliate->user_id );

            $externalIds = [];

            if ( $campaignsData && is_array($campaignsData) )
            {
                foreach ( $campaignsData AS $campaignData )
                {
                    $externalIds[] = $campaignData['ext_id'];

                    $campaign = models\Campaigns::findOne([ 
                        'ext_id'        => $campaignData['ext_id'],
                        'Affiliates_id' => $affiliate->id
                    ]);

                    if  ( $campaign )
                    {
                        $newCampaign = false;

                        $this->_checkChanges( $rule['class'], $campaign, $campaignData );

                        if ( 
                            $campaign->landing_url != $campaignData['landing_url'] 
                            && $this->_redis->exists( 'campaign:'.$campaign->id ) 
                        )
                        {
                            $this->_redis->hset( 'campaign:'.$campaign->id,
                                'callback', 
                                $campaignData['landing_url']
                            );                  
                        }

                        switch ( $campaign->status )
                        {
                            case 'archived':
                            case 'paused':
                            break;
                                $campaign->status = $campaignData['status'];
                            default:
                        }                        
                    }
                    else
                    {
                        $newCampaign      = true;
                        $campaign         = new models\Campaigns;     
                        $campaign->status = $campaignData['status'];    
                    }                    

                    $campaign->Affiliates_id = $affiliate->id;
                    $campaign->name          = $campaignData['name'];
                    $campaign->ext_id        = $campaignData['ext_id'];
                    $campaign->info          = $campaignData['desc'];
                    $campaign->payout        = (float)$campaignData['payout'];
                    $campaign->landing_url   = $campaignData['landing_url'];

                    if ( $campaignData['package_id'] )
                        $campaign->app_id      = json_encode($campaignData['package_id']);
                    else
                        $campaign->app_id      = null;

                    if ( $campaignData['country'] )
                        $campaign->country      = json_encode($campaignData['country']);
                    else
                        $campaign->country      = null;

                    if ( $campaignData['device_type'] )
                        $campaign->device_type  = json_encode($campaignData['device_type']);
                    else
                        $campaign->device_type  = null;

                    if ( $campaignData['os'] )
                        $campaign->os           = json_encode($campaignData['os']);    
                    else
                        $campaign->os           = null;

                    if ( $campaignData['os_version'] )
                        $campaign->os_version   = json_encode($campaignData['os_version']);
                    else
                        $campaign->os_version   = null;


                    if ( $campaignData['carrier'] )
                        $campaign->carrier      = json_encode($campaignData['carrier']);
                    else
                        $campaign->carrier      = null;


                    if ( $campaignData['connection_type'] )
                        $campaign->connection_type = json_encode($campaignData['connection_type']);
                    else
                        $campaign->connection_type = null;                                         

                    if ( !$campaign->save() )
                    {
                        $this->_createAlert(  $rule['class'], $campaign->getErrors(), $api->getStatus(), json_encode($campaignData) );
                    }

                    $this->_redis->hmset( 'campaign:'.$campaign->id, [
                        'callback'    => $campaign->landing_url,
                        'click_macro' => $affiliate->click_macro,
                        'placeholders' => $affiliate->placeholders,
                        'ext_id'      => $campaign->ext_id
                    ]);

                    unset( $campaign );
                }
            }
            else
            {
                $this->_createAlert( $rule['class'], $api->getMessages(), $api->getStatus() );
            }

            $this->_clearCampaigns( $rule['affiliate_id'], $externalIds,  $rule['class'] );            
        }
        catch ( Exception $e )
        {
            $msg = 'exception';
            $this->_createAlert(  $rule['class'], $msg, $api->getStatus() );
        }

        unset ( $api );
        unset ( $affiliate );    
    }


    protected function _checkChanges ( $api_class, $campaign, array $campaignData )
    {
    	$changes = '';
    	$this->_changed = false;

    	if ( $campaign->payout != $campaignData['payout'] )
		{
			$this->_changed = true;
			$changes .= '<td>'.$campaign->payout.' => '. $campaignData['payout'].'</td>';
		}   			
		else
		{
			$changes .= '<td>&nbsp;</td>';
		}


		$changes .= '
			<td>'.$this->_listChanges( json_decode($campaign->country), $campaignData['country'] ).'</td>
			<td>'.$this->_listChanges( json_decode($campaign->carrier), $campaignData['carrier'] ).'</td>
			<td>'.$this->_listChanges( json_decode($campaign->connection_type), $campaignData['connection_type'] ).'</td>
			<td>'.$this->_listChanges( json_decode($campaign->device_type), $campaignData['device_type'] ).'</td>
			<td>'.$this->_listChanges( json_decode($campaign->os), $campaignData['os'] ).'</td>
			<td>'.$this->_listChanges( json_decode($campaign->os_version), $campaignData['os_version'] ).'</td>
		';


		if ( $campaign->status != $campaignData['status'] )
		{
			$this->_changed = true;
			$changes .= '<td>'.$campaign->status .' => ' .$campaignData['status'].'</td>';
		}
		else
		{
			$changes .= '<td>&nbsp;</td>';
		}

        $newPackageIds = $campaignData['package_id'] ? $campaignData['package_id'] : [];
        $oldPackageIds = $campaign->app_id ? (array)json_decode($campaign->app_id) : [];

        $packagesDiff = array_diff ( $oldPackageIds, $newPackageIds ) + array_diff( $newPackageIds, $oldPackageIds );
      
        if ( !empty($packagesDiff) )
            $this->_changed = true;


    	if ( $this->_changed )
    	{
            $clusters             = [];
            $clustersHasCampaigns = models\ClustersHasCampaigns::findAll( ['Campaigns_id' => $campaign->id] );

            foreach ( $clustersHasCampaigns as $assign )
            {
                $clusters[] = $assign['Clusters_id'];

                switch ( $campaignData['status'] )
                {
                    case 'active':
                        $addPackageIds = array_diff( $newPackageIds, $oldPackageIds );
                        $remPackageIds = array_diff( $oldPackageIds, $newPackageIds );

                        foreach ( $addPackageIds AS $os => $packageId )
                        {
                            $this->_redis->zadd( 'clusterlist:'.$assign['Clusters_id'], 
                                $assign['delivery_freq'],
                                $campaign->id.':'.$campaign->affiliates->id.':'.$packageId
                            );
                        } 

                        foreach ( $remPackageIds AS $os => $packageId )
                        {
                            $this->_redis->zrem( 'clusterlist:'.$assign['Clusters_id'], 
                                $campaign->id.':'.$campaign->affiliates->id.':'.$packageId
                            );
                        }                                                    
                    break;
                    default:
                        $packageIds = array_merge( $oldPackageIds, $newPackageIds );

                        foreach ( $packageIds AS $os => $packageId )
                        {
                            $this->_redis->zrem( 'clusterlist:'.$assign['Clusters_id'], 
                                $campaign->id.':'.$campaign->affiliates->id.':'.$packageId
                            );
                        }
                    break;
                }

            }

            if ( !empty($clusters) )
            {
                $this->_changes .= '
                    <tr>
                        <td>'.$api_class.'</td>
                        <td>'.$campaign->id.'</td>
                        <td>'.$campaign->ext_id.'</td>
                        '.$changes.',
                        <td>'.json_encode( $clusters ).'</td>
                    </tr>
                ';                          
            }
    	}

    }


    protected function _clearCampaigns ( $affiliate_id, array $external_ids, $api_class )
    {
        $campaigns = models\Campaigns::find()->where(['Affiliates_id' => $affiliate_id ])->andWhere(['<>', 'status', 'archived'])->andWhere(['<>', 'status', 'paused'])->andWhere(['<>', 'status', 'aff_paused'])->andWhere( ['not in' , 'ext_id', $external_ids] )->all();

        foreach ( $campaigns AS $campaign )
        {
            if ( !in_array( $campaign->ext_id, $external_ids ) )
            {
                $clustersHasCampaigns = models\ClustersHasCampaigns::findAll(['Campaigns_id' => $campaign->id]);

                $clusters = [];

                foreach ( $clustersHasCampaigns as $assign )
                {
                    $clusters[] = $assign['Clusters_id'];
                    $value = "[".$campaign->id.':'.$campaign->affiliates->id;
                    $this->_redis->zremrangebylex( 'clusterlist:'.$assign['Clusters_id'], $value, $value."\xff" );
                }

                $prevStatus =  $campaign->status;

                $campaign->status = 'aff_paused';
                $campaign->save();

                if ( !empty($clusters) )
                {
                    $this->_changes .= '
                        <tr>
                            <td>'.$api_class.'</td>
                            <td>'.$campaign->id.'</td>
                            <td>'.$campaign->ext_id.'</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>'.$prevStatus.' => aff_paused</td>
                            <td>'.implode( ',', $clusters ).'</td>
                        </tr>
                    ';                    
                }

                unset( $clusters );
                unset( $clustersHasCampaigns );                   
            }
        }
    }


    protected function _listChanges ( array $list1 = null, array $list2 = null )
    {
    	$changes = '';

    	if ( !$list1 )
    		$list1 = [];

    	if ( !$list2 )
    		$list2 = [];

    	$first = true;
		foreach ( array_diff($list2,$list1) AS $added )
		{
			if ( $first ){
				$first    = false;
				$changes .= '<b>Added: </b>';
			}
			else
				$changes .= ', ';

			$changes .= $added;
		}

		if ( $changes != '' )
			$changes .= '<br><br>';

		$first = true;
		foreach ( array_diff($list1,$list2) AS $removed )
		{
			if ( $first ){
				$first    = false;
				$changes .= '<b>Removed: </b>';
			}
			else
				$changes .= ', ';

			$changes .= $removed;
		}

		if ( $changes != '' )
			$this->_changed = true;

		return $changes;
    }



    protected function _createAlert ( $api_class, $messages, $status, $data = null )
    {
    	if ( is_array( $messages ) )
    	{
    		foreach ( $messages as $message )
    		{
    			$this->_alerts .= '
		    		<tr>
		    			<td>'.$api_class.'</td>
		    			<td>'.$status.'</td>
		    			<td>'.implode('<br>', $message).'</td>
		    			<td>'.$data.'</td>
		    		</tr>
    			';
    		}
    	}
    	else
    	{
			$this->_alerts .= '
	    		<tr>
	    			<td>'.$api_class.'</td>
	    			<td>'.$status.'</td>	    			
	    			<td>'.$messages.'</td>
		    		<td>'.$data.'</td>	    			
	    		</tr>
			';    		
    	}
    }


    protected function _sendAlerts( )
    {
    	if ( $this->_alerts && $this->_alerts != '' )
    	{
			$html = '
	            <html>
	                <head>
	                	<style>
	                		td {
	                			padding:10px;
	                			border:1px solid;
	                		}
	                		table{
	                			border:1px solid;
	                			border-collapse:collapse;
	                		}
	                	</style>
	                </head>
	                <body>
	                    <table>
	                        <thead>
	                            <td>API</td>
	                            <td>HTTP STATUS</td>	                            
	                            <td>MESSAGE</td>
	                            <td>PARAMS</td>
	                        </thead>
	                        <tbody>'.$this->_alerts.'</tbody>
	                    </table>
	                </body>
	            </html>		
			';

			$this->_sendMail( 
				self::FROM, 
				self::ALERTS_INBOX, 
				'AFFILIATES API ERROR '.date('Y-m-d'),  
				$html 
			);

			return $this->_alerts;
    	}  	

        return '<tr><td style="-webkit-column-span: all;column-span: all;">No errors</td></tr>';
    }


    protected function _sendNotifications( )
    {
    	if ( $this->_changes && $this->_changes != '' )
    	{
			$html = '
	            <html>
	                <head>
	                	<style>
	                		td {
	                			padding:10px;
	                			border:1px solid;
	                		}
	                		table{
	                			border:1px solid;
	                			border-collapse:collapse;
	                		}
	                	</style>	                
	                </head>
	                <body>
	                    <table>
	                        <thead>
	                            <td>API</td>
	                            <td>CAMPAIGN ID</td>
	                            <td>EXT ID</td>
	                            <td>PAYOUT</td>
	                            <td>COUNTRY</td>
	                            <td>CARRIER</td>
	                            <td>CONNECTION</td>
	                            <td>DEVICE</td>
	                            <td>OS</td>
	                            <td>OS VERSION</td>
	                            <td>STATUS</td>
	                           	<td>AFFECTED CLUSTERS</td>
	                        </thead>
	                        <tbody>'.$this->_changes.'</tbody>
	                    </table>
	                </body>
	            </html>		
			';

			$this->_sendMail( 
				self::FROM, 
				self::NOTIFY_INBOX, 
				'CAMPAIGN CHANGES '.date('Y-m-d'),  
				$html 
			);

			return $this->_changes;
    	}  	

        return '<tr><td  style="-webkit-column-span: all;column-span: all;">No important changes</td></tr>';
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



}