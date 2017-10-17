<?php

	namespace backend\components;

	use Yii;
	use yii\base\Component;
	use app\models;
	use yii\base\InvalidConfigException;
	 
	class MobildaAPI extends Component
	{
		// uses mars media plattform
		const URL = 'http://s.marsfeeds.com/xml/cpa_feeds/feed.php?format=json';

		protected $_msg;
		protected $_status;

		public function requestCampaigns ( $api_key, $user_id = null  )
		{
			$url    = self::URL . '&feed_id'.$user_id.'&hash='.$api_key;
			$curl   = curl_init($url);

			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

			$json_response = curl_exec($curl);

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
			else if ( !isset($response->products) || empty($response->products) )
			{
				$this->_msg = 'No campaign data in response';
				return false;				
			}

			$result = [];
			$dbCarriers = models\Carriers::find()->all();

			foreach ( $response->products AS $campaign )
			{
				if ( isset($campaign->targeting->countries ) && $campaign->targeting->countries && is_array($campaign->targeting->countries) )
				{
					$countries = $campaign->targeting->countries;
				}
				else
				{
					$countries = explode( ',' , $campaign->targeting->countries );
				}			

				if ( isset( $campaign->mobile_attributes->allowed_devices ) )
				{
					$deviceTypes = ApiHelper::getDeviceTypes($campaign->mobile_attributes->allowed_devices, false);				
					$oss		 = ApiHelper::getOs($campaign->mobile_attributes->allowed_devices, false);
				}
				else
				{
					$deviceTypes = [];
					$oss 		 = [];
				}


				if ( isset( $campaign->mobile_attributes->allowed_carriers ) )
				{
					$carriers = ApiHelper::getCarriers( $campaign->mobile_attributes->allowed_carriers, $dbCarriers );				
				}
				else
				{
					$carriers = [];
				}				


				if ( isset( $campaign->mobile_attributes->MinOs_version ) )
				{
					$osVersion = $campaign->mobile_attributes->MinOs_version;
				}
				else
				{
					$osVersion = [];
				}


				if ( isset( $campaign->mobile_attributes->mobile_support ) )
				{
					$connType = [ 'Carrier', 'WiFi' ];
				}
				else
				{
					$connType = [ 'WiFi' ];
				}				

				switch ( strtolower($campaign->attributes->status) )
				{
					case 'active':
					case 'live':
						$status = 'active';
					break;
					default:
						$status = 'aff_paused';
					break;
				}

				if ( $campaign->attributes->preview_url )
				{
					$packageIds = ApiHelper::getAppIdFromUrl( $campaign->attributes->preview_url );

					if ( $campaign->attributes->package_name )
					{
						if ( isset($packageIds['android']) )
						{
							$packageIds['android'] = $campaign->attributes->package_name;
						}
						else if ( isset($packageIds['ios']) )
						{
							$packageIds['ios'] = $campaign->attributes->package_name;
						}
						else if ( !in_array( 'Android', $oss ) )
						{
							$packageIds['android'] = $campaign->attributes->package_name;
						}
						else if ( !in_array( 'iOS', $oss ) )
						{
							$packageIds['ios'] = $campaign->attributes->package_name;
						}
					}					
				}
				else if ( in_array( 'Android', $oss ) && $campaign->attributes->package_name )
				{
					$packageIds = [
						'android' => $campaign->attributes->package_name
					];
				}
				else if ( in_array( 'iOS', $oss ) && $campaign->attributes->package_name )
				{
					$packageIds = [
						'ios' => $campaign->attributes->package_name
					];
				}				
				else
				{
					$packageIds = [];
				}
				
				$country = [];

				foreach  ( $countries as $code )
				{
					switch ( $code )
					{
						case 'UK':
							$country[] = 'GB';
						break;
						default:
							$country[] = $code;
						break;
					}
				}

				$result[] = [
					'ext_id' 			=> $campaign->attributes->id,
					'name'				=> $campaign->attributes->title,
					'desc'				=> preg_replace('/[\xF0-\xF7].../s', '', $campaign->attributes->description),
					'payout' 			=> $campaign->attributes->rate,
					'landing_url'		=> $campaign->attributes->tracking_url,
					'country'			=> empty($country) ? null : $country,
					'device_type'		=> empty( $deviceTypes ) ? null : $deviceTypes,
					'connection_type'	=> empty( $connType ) ? null : $connType,
					'carrier'			=> empty( $carriers ) ? null : $carriers,
					'os'				=> empty( $oss ) ? null : $oss,
					'os_version'		=> empty( $osVersion ) ? null : $osVersion,
					'package_id'		=> empty($packageIds) ? null : $packageIds,
					'status'			=> $status,
					'currency'			=> $campaign->attributes->currency
				];

				unset( $campaign );
				unset( $country );
				unset( $deviceTypes );
				unset( $oss );
				unset( $countries );
				unset( $packageIds );
				unset( $connType );	
				unset( $carriers );			
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