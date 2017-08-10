<?php

namespace backend\controllers;

use Yii;
use app\models;
use Predis;
use backend\components;

class AffiliatesapiController extends \yii\web\Controller
{
	const NOTIFY_INBOX = '';
	const ALERTS_INBOX = '';

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
		];
	}


    public function actionIndex()
    {	
    	$this->_changes = '';
    	$this->_alerts  = '';
    	$this->_redis 	= new \Predis\Client( \Yii::$app->params['predisConString'] );

    	foreach ( $this->_apiRules() AS $rule )
    	{
    		$className  = 'backend\components\\'.$rule['class'];
    		$api 		= new $className;
    		$affiliate  = models\Affiliates::findOne( ['id' => $rule['affiliate_id'] ] );

    		try
    		{
    			$campaignsData  = $api->request( $affiliate->api_key, $affiliate->user_id );

    			if ( $campaignsData && is_array($campaignsData) )
    			{
    				foreach ( $campaignsData AS $campaignData )
    				{
    					$campaign = models\Campaigns::findOne([ 
    						'ext_id' 		=> $campaignData['ext_id'],
    						'Affiliates_id' => $affiliate->id
    					]);

    					if  ( $campaign )
    						$this->_checkChanges( $rule['class'], $campaign, $campaignData );
    					else
    						$campaign = new models\Campaigns;

    					$campaign->Affiliates_id = $affiliate->id;
    					$campaign->name    		 = $campaignData['name'];
    					$campaign->status  		 = $campaignData['status'];
    					$campaign->ext_id  		 = $campaignData['ext_id'];
    					$campaign->payout  		 = (float)$campaignData['payout'];
    					$campaign->landing_url   = $campaignData['landing_url'];

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

    					//var_export($campaign);die();
    					
    					if ( !$campaign->save() )
    						$this->_createAlert(  $rule['class'], $campaign->getErrors(), $api->getStatus(), json_encode($campaignData) );

    					unset( $campaign );				
    				}
    			}
    			else
    			{
    				$this->_createAlert( $rule['class'], $api->getMessages(), $api->getStatus() );
    				continue;
    			}
    		}
    		catch ( Exception $e )
    		{
    			$msg = 'exception';
    			$this->_createAlert(  $rule['class'], $msg, $api->getStatus() );
    			continue;
    		}

    		unset ( $api );
    		unset ( $affiliate );
    	}

    	$this->_sendAlerts();
    	$this->_sendNotifications();

        //return $this->render('index');
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


    	if ( $this->_changed )
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

			foreach ( $clustersHasCampaigns as $assign )
			{
				$clusters[] = $assign['Clusters_id'];

				if ( $campaign->status != $campaignData['status'] )
				{
					$this->_redis->zadd( 'clusterlist:'.$assign['Clusters_id'], $status, $campaign->id );
				}
			}

			$changes .= '<td>'.implode( ',', $clusters ).'</td>';
    	}
    	else
    	{
    		$changes .= '<td>&nbsp;</td>'; 
    	}

    	if ( $this->_changed )
    	{
	    	$this->_changes .= '
	    		<tr>
	    			<td>'.$api_class.'</td>
	    			<td>'.$campaign->id.'</td>
	    			<td>'.$campaign->ext_id.'</td>
	    			'.$changes.'
	    		</tr>
	    	';    		
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
				'no-reply@spladx.co', 
				self::ALERTS_INBOX, 
				'AFFILIATES API ERROR '.date('Y-m-d'),  
				$html 
			);

			echo $html.'<hr>';
    	}  	
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
				'no-reply@spladx.co', 
				self::NOTIFY_INBOX, 
				'CAMPAIGN CHANGES '.date('Y-m-d'),  
				$html 
			);

			echo $html.'<hr>';
    	}  	
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

}