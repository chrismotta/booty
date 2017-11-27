<?php

	namespace backend\components;

	use Yii;
	use yii\base\Component;
	use app\models;	
	use yii\base\InvalidConfigException;

	class ResultsMediaAPI extends Component
	{
		const URL = 'http://s.resultsmedia.com/xml/cpa_feeds/feed.php?format=json';

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

			$url  = self::URL.'&feed_id='.$user_id.'&hash='.$api_key;
			$curl = curl_init();

			curl_setopt($curl, CURLOPT_URL, $url );
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
			else if ( !isset($response->products[0]->attributes->id) )
			{
				$this->_msg = 'No campaign data in response';
				return false;
			}
			else
			{
				$next = true;
			}

			$dbCarriers = models\Carriers::find()->all();

			$result = [];

			while ( $next )
			{
				foreach ( $response->products as $campaign )
				{
					$packageIds  = [];
					$reqCarriers = [];
					$countries   = [];
					$devices     = [];

					if ( $campaign->attributes->preview_url )
					{
						$packageIds = ApiHelper::getAppIdFromUrl( $campaign->attributes->preview_url );	
					}
					else
					{
						$packageIds = [];
					}	

					if ( isset($campaign->targeting->countries[0]) )
					{
						foreach ( $campaign->targeting->countries as $code )
						{
							$countries[] = $code;
						}
					}
					else
					{
						$countries = [];
					}

					if ( isset($campaign->mobile_attributes->allowed_devices[0]) )
					{
						foreach ( $campaign->mobile_attributes->allowed_devices as $device )
						{
							$devices[] = $device;
						}
					}
					else
					{
						$devices = [];
					}

					if ( isset($campaign->mobile_attributes->allowed_carriers[0]) )
					{
						foreach ( $campaign->mobile_attributes->allowed_devices as $carrier )
						{
							$reqCarriers[] = $carrier;
						}
					}
					else
					{
						$reqCarriers = [];
					}				

					$carriers 	 = ApiHelper::getCarriers( $reqCarriers, $dbCarriers );
					$deviceTypes = ApiHelper::getDeviceTypes($devices, false);
					$oss 		 = ApiHelper::getOs($devices, false);

					$country = [];

					foreach  ( $countries as $code )
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

					$result[] = [	
						'ext_id' 			=> $campaign->attributes->id,
						'name'				=> $campaign->attributes->title,
						'desc'				=> preg_replace('/[\xF0-\xF7].../s', '', $campaign->attributes->description), 
						'payout' 			=> (float)$campaign->attributes->rate,
						'landing_url'		=> $campaign->attributes->tracking_url,
						'country'			=> empty($country) ? null : $country,
						'device_type'		=> empty($deviceTypes) ? null : $deviceTypes,
						'connection_type'	=> null,
						'carrier'			=> null,
						'os'				=> empty($oss) ? null : $oss,
						'os_version'		=> null,
						'package_id'		=> empty($packageIds) ? null : $packageIds,
						'status'			=> 'active',
						'currency'			=> $campaign->attributes->currency
					];

					unset ( $country );
					unset ( $countries );
					unset ( $deviceTypes);
					unset ( $devices);
					unset ( $os );
					unset ( $packageIds );
					unset ( $reqCarriers );
				}

				if ( isset($response->summary->current_page) && isset($response->summary->total_pages) && (int)$response->summary->current_page<(int)$response->summary->total_pages)
				{
					$url2 = $url.'&page='.$response->summary->current_page+1;
					curl_setopt($curl, CURLOPT_URL, $url2 );
					
					$json_response = curl_exec($curl);
					$response = json_decode($json_response);

					if ( $response && isset($response->products[0]->attributes->id) )
						$next = true;
					else
						$next = false;									
				}
				else
				{
					$next = false;
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