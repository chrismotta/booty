<?php

	namespace backend\components;

	use Yii;
	use yii\base\Component;
	use yii\base\InvalidConfigException;
	 
	class RegamingAPI extends Component
	{
		// uses orangear.com plattform
		const URL = 'http://api.regaming.com/affiliate/offer/findAll/?approved=1';

		protected $_msg;
		protected $_status;

		public function requestCampaigns ( $api_key, $user_id = null  )
		{
			$url    = self::URL . '&token='.$api_key;
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
			else if ( isset($response->error_messages) && $response->error_messages )
			{
				$this->_msg = $response->error_messages;
				return false;
			}
			else if ( !isset($response->offers) )
			{
				$this->_msg = 'No campaign data in response';
				return false;				
			}

			$result = [];
		
			foreach ( $response->offers AS $ext_id => $campaign )
			{
				if ( isset($campaign->Countries) && $campaign->Countries && is_array($campaign->Countries) )
				{
					$countries = $campaign->Countries;
				}
				else
				{
					$countries = explode( ',' , $campaign->Countries );
				}			

				$oss		 = ApiHelper::getOs($campaign->Platforms, false);
				$deviceTypes = ApiHelper::getDeviceTypes($campaign->Platforms, false);				

				switch ( strtolower($campaign->Status) )
				{
					case 'active':
						$status = 'active';
					break;
					default:
						$status = 'aff_paused';
					break;
				}

				if ( $campaign->Preview_url )
				{
					$packageIds = ApiHelper::getAppIdFromUrl( $campaign->Preview_url );

					if ( $campaign->APP_ID )
					{
						if ( isset($packageIds['android']) )
						{
							$packageIds['android'] = $campaign->APP_ID;
						}
						else if ( isset($packageIds['ios']) )
						{
							$packageIds['ios'] = $campaign->APP_ID;
						}
						else if ( !in_array( 'Android', $oss ) )
						{
							$packageIds['android'] = $campaign->APP_ID;
						}
						else if ( !in_array( 'iOS', $oss ) )
						{
							$packageIds['ios'] = $campaign->APP_ID;
						}
					}					
				}
				else if ( in_array( 'Android', $oss ) && $campaign->APP_ID )
				{
					$packageIds = [
						'android' => $campaign->APP_ID
					];
				}
				else if ( in_array( 'iOS', $oss ) && $campaign->APP_ID )
				{
					$packageIds = [
						'ios' => $campaign->APP_ID
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
					'ext_id' 			=> $ext_id,
					'name'				=> $campaign->Name,
					'desc'				=> preg_replace('/[\xF0-\xF7].../s', '', $campaign->Description),
					'payout' 			=> $campaign->Payout,
					'landing_url'		=> $campaign->Tracking_url,
					'country'			=> $country,
					'device_type'		=> $deviceTypes,
					'connection_type'	=> null,
					'carrier'			=> null,
					'os'				=> $oss,
					'os_version'		=> null,
					'package_id'		=> empty($packageIds) ? null : $packageIds,
					'status'			=> $status,
					'currency'			=> $campaign->Currency
				];

				unset( $campaign );
				unset( $country );
				unset( $deviceTypes );
				unset( $oss );
				unset( $countries );
				unset( $packageIds );				
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