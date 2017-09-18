<?php

	namespace backend\components;

	use Yii;
	use yii\base\Component;
	use app\models;
	use yii\base\InvalidConfigException;
	 
	class AppclientsAPI extends Component
	{
		// uses hasoffers.com plattform
		const URL = 'https://www.appclients.mobi/ui/?module=campaigns-export&content_type=json&v=1.8';

		protected $_msg;
		protected $_status;

		public function requestCampaigns ( $api_key, $user_id = null  )
		{
			$url    = self::URL . '&api_token='.$api_key;
			$curl   = curl_init();

			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

			$response = json_decode(curl_exec($curl));

			$this->_status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

			if  ( isset($_GET['source']) && $_GET['source']==1 )
			{
				header('Content-Type: text/json');
				echo json_encode( $response, JSON_PRETTY_PRINT );
				die();
			}			

			if ( !$response )
			{
				$this->_msg = 'Response without body';
				return false;
			}

			if ( isset($response->errorMessage) && $response->errorMessage )
			{
				$this->_msg = $response->errorMessage;				
				return false;
			}

			foreach ( $response AS $campaign )
			{
				if ( isset($campaign->campaign_products[0]->pay_rate) )
				{

					if ( isset($campaign->campaign_display_rules->min_device_os_version) )
						$osVersion  = ApiHelper::getValues($campaign->campaign_display_rules->min_device_os_version);

					if ( isset($campaign->campaign_display_rules->device_os) )
						$os 		= ApiHelper::getOs($campaign->campaign_display_rules->device_os);

					if ( isset($campaign->campaign_display_rules->device_type->whitelist) )
						$devices 	= ApiHelper::getDeviceTypes( $campaign->campaign_display_rules->device_type->whitelist, '\\' );

					$url = null;
					$packageIds = [];

					foreach ( $campaign->creatives AS $creative )
					{
						if ( $creative->url )
						{
							$url = $creative->url;
							break;
						}
					}

					if ( !empty($os) && $os[0]!='Other'  && $campaign->package_name )
					{
						$osName = strtolower($os[0]);
						$packageIds[$osName] = $campaign->package_name;
					}

					$result[] = [
						'ext_id' 			=> $campaign->campaign_id, 
						'name'				=> $campaign->name, 
						'desc'				=> preg_replace('/[\xF0-\xF7].../s', '', $campaign->instructions), 
						'payout' 			=> (float)$campaign->campaign_products[0]->pay_rate, 
						'landing_url'		=> $url, 
						'country'			=> $campaign->campaign_country_target, 
						'device_type'		=> $devices, 
						'connection_type'	=> null, 
						'carrier'			=> null, 
						'os'				=> empty($os) ? null : $os, 
						'os_version'		=> empty($osVersions) ? null : $osVersions, 
						'package_id'		=> empty($packageIds) ? null : $packageIds,
						'status'			=> 'active', 	
						'currency'			=> $campaign->campaign_products[0]->currency
					];

					unset( $campaign );
					unset( $osVersion );
					unset( $countries );
					unset( $packageIds );
					unset( $devices );
				}
			}

			if  ( isset($_GET['test']) && $_GET['test']==1 )
			{
				header('Content-Type: text/json');
				echo json_encode( $result, JSON_PRETTY_PRINT );
				die();
			}

			return $result;
		}


		public function getMessages ( )
		{
			return $this->_msg;
		}


		public function getStatus ( )
		{
			return $this->_status;
		}

	}

?>