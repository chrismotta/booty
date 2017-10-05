<?php

	namespace backend\components;

	use Yii;
	use yii\base\Component;
	use yii\base\InvalidConfigException;
	 
	class ClicksmobAPI extends Component
	{
		// uses orangear.com plattform
		const URL = 'https://api.clicksmob.com/api/v2/services/offers.json?featureList=S.T';

		protected $_msg;
		protected $_status;

		public function requestCampaigns ( $api_key, $user_id = null  )
		{
			$url    = self::URL . '&uid='.$user_id.'&utoken='.$api_key;
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
			else if ( !isset($response->offer) )
			{
				$this->_msg = 'No campaign data in response';
				return false;
			}			

			$result = [];

			foreach ( $response->offer AS $campaign )
			{
				$connectionType = [];
				$packageIds     = [];

				if ( (int)$campaign->allowedWiFi==1 && !in_array('Wifi', $connectionType) )		
					$connectionType[] = 'Wifi';


				if ( (int)$campaign->allowed3G==1 && !in_array('Carrier', $connectionType) )	
					$connectionType[] = 'Carrier';


				if ( $campaign->iosbundleID )
				{
					$packageIds['ios'] = $campaign->iosbundleID;
				}

				if ( $campaign->androidPackageName )
				{
					$packageIds['android'] = $campaign->androidPackageName;
				}				

				if ( $campaign->offerPayouts )
				{
					foreach ( $campaign->offerPayouts->offerPayout AS $payout )
					{
						if ( isset($payout->countries->country) && $payout->countries->country && is_array($payout->countries->country) )
						{
							$countries = $payout->countries->country;
						}
						else
						{
							$countries = explode( ',' , $payout->countries->country );
						}

						$deviceTypes = ApiHelper::getDeviceTypes($payout->platforms->platform, false);
						$oss 		 = ApiHelper::getOs($payout->platforms->platform, false);

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
							'ext_id' 			=> $payout->id,
							'name'				=> $campaign->offerName,
							'desc'				=> preg_replace('/[\xF0-\xF7].../s', '', $campaign->description),
							'payout' 			=> $payout->payout,
							'landing_url'		=> $campaign->targetURL,
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