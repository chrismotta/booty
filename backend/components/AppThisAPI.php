<?php

	namespace backend\components;

	use Yii;
	use yii\base\Component;
	use yii\base\InvalidConfigException;
	 
	class AppThisAPI extends Component
	{
		// uses orangear.com plattform
		const URL = 'http://feed.appthis.com/feed/v1?format=json';

		protected $_msg;
		protected $_status;

		public function requestCampaigns ( $api_key, $user_id = null  )
		{
			$url    = self::URL . '&api_key='.$api_key;
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
			else if ( !isset($response->offers) || empty($response->offers) )
			{
				$this->_msg = 'No campaign data in response';
				return false;
			}			

			$result = [];

			foreach ( $response->offers AS $campaign )
			{
				$connectionType = [];
				$packageIds     = [];


				if ( $campaign->ios_bundle_id )
				{
					$packageIds['ios'] = ApiHelper::cleanAppleId($campaign->ios_bundle_id);
				}

				if ( $campaign->android_package_name )
				{
					$packageIds['android'] = $campaign->android_package_name;
				}				

				if ( $campaign->campaigns )
				{
					foreach ( $campaign->campaigns AS $cp )
					{
						if ( isset($cp->countries) && $cp->countries && is_array($cp->countries) )
						{
							$countries = $cp->countries;
						}
						else
						{
							$countries = explode( ',' , $cp->countries );
						}

						$deviceTypes = ApiHelper::getDeviceTypes($cp->platform, false);
						$oss 		 = ApiHelper::getOs($cp->platform, false);

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
							'ext_id' 			=> $campaign->id.':'.$cp->id,
							'name'				=> $campaign->name,
							'desc'				=> preg_replace('/[\xF0-\xF7].../s', '', $campaign->description),
							'payout' 			=> $cp->payout,
							'landing_url'		=> $campaign->tracking_url,
							'country'			=> $country,
							'device_type'		=> empty($deviceTypes) ? null : $deviceTypes,
							'connection_type'	=> empty($connectionType) ? null : $connectionType,
							'carrier'			=> null,
							'os'				=> $oss,
							'os_version'		=> null,
							'package_id'		=> empty($packageIds) ? null : $packageIds,
							'status'			=> 'active',
							'currency'			=> 'USD'
						];

						unset ( $oss );
						unset ( $deviceTypes);
						unset ( $country );
					}
				}
			}

			unset ( $connectionType );
			unset ( $packageIds );

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