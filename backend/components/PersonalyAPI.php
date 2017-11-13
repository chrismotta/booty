<?php

	namespace backend\components;

	use Yii;
	use yii\base\Component;
	use yii\base\InvalidConfigException;
	 
	class PersonalyAPI extends Component
	{
		const URL = 'https://dsp.persona.ly/api/campaigns?simplify=true';

		protected $_msg;
		protected $_status;

		public function requestCampaigns ( $api_key, $user_id = null  )
		{
			// ONLY FOR TESTING
			/*
			$result[] = [
				'ext_id' 			=> 'test_apush_id',
				'name'				=> 'test_apush',
				'desc'				=> 'bla bla', 
				'payout' 			=> 1,
				'landing_url'		=> 'http://google.com',
				'country'			=> ['US'],
				'device_type'		=> ['tablet'],
				'connection_type'	=> ['WiFi'],
				'carrier'			=> null,
				'os'				=> ['iOS'],
				'os_version'		=> null,
				'package_id'		=> ['ios'=>"4444444"],
				'status'			=> 'active',
				'currency'			=> 'USD'
			];

			return $result;
			*/
			
			$url  = self::URL.'&token='.$api_key;
			$curl = curl_init($url);

			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

			$json_response = curl_exec($curl);

			if  ( isset($_GET['raw']) && $_GET['raw']==1 )
			{
				header('Content-Type: text/json');
				echo json_encode( $json_response, JSON_PRETTY_PRINT );
				die();
			}

			$response = json_decode($json_response);
			
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
			else if ( !isset($response->campaigns[0]->id) )
			{
				$this->_msg = 'No campaign data in response';
				return false;
			}
			else
			{
				$next = true;
			}

			$result = [];

			foreach ( $response->campaigns as $campaign )
			{
				$packageIds = [];

				if ( $campaign->store_app_id )
				{
					$packageIds['ios'] = $campaign->store_app_id;
				}
				else if ( $campaign->preview_url_ios )
				{

					$packageIds = array_merge( $packageIds, ApiHelper::getAppIdFromUrl( $campaign->preview_url_ios ) );
				}

				if ( $campaign->android_package_id )
				{
					$packageIds['android'] = $campaign->android_package_id;
				}
				else if ( $campaign->preview_url_ios )
				{

					$packageIds = array_merge( $packageIds, ApiHelper::getAppIdFromUrl( $campaign->preview_url_android ) );
				}
	
				$country = [];
				if ( isset($campaign->payouts->countries[0]) )
				{
					foreach ( $campaign->payouts->countries as $code )
					{
						switch ( strtoupper($code) )
						{
							case 'UK':
								$country[] = 'GB';
							break;
							default:
								$country[] = strtoupper($code);
							break;
						}
					}
				}

				if ( isset($campaign->payouts->platform) )
				{
					$deviceTypes = ApiHelper::getDeviceTypes($campaign->payouts->platform, false);
					$oss 		 = ApiHelper::getOs($campaign->payouts->platform, false);
				}
				else
				{
					$deviceTypes = [];
					$oss         = [];
				}				


				$result[] = [	
					'ext_id' 			=> $campaign->id,
					'name'				=> $campaign->campaign_name,
					'desc'				=> null,
					'payout' 			=> (float)$campaign->payouts->usd_payout,
					'landing_url'		=> $campaign->tracking_url,
					'country'			=> empty($country) ? null : $country,
					'device_type'		=> empty($deviceTypes) ? null : $deviceTypes,
					'connection_type'	=> null,
					'carrier'			=> null,
					'os'				=> empty($oss) ? null : $oss,
					'os_version'		=> null,
					'package_id'		=> empty($packageIds) ? null : $packageIds,
					'status'			=> 'active',
					'currency'			=> 'USD'
				];

				unset ( $country );
				unset ( $countries );
				unset ( $deviceTypes);
				unset ( $devices);
				unset ( $os );						
				unset ( $packageIds );
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