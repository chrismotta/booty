<?php

	namespace backend\components;

	use Yii;
	use yii\base\Component;
	use yii\base\InvalidConfigException;
	 
	class ClicksmobAPI extends Component
	{
		// uses orangear.com plattform
		const URL = 'https://api.clicksmob.com/api/v2/services/offers.json?uid=15027&featureList=S.T';

		protected $_msg;
		protected $_status;

		public function requestCampaigns ( $api_key, $user_id = null  )
		{
			$url    = self::URL . '&utoken='.$api_key;
			$curl   = curl_init($url);

			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

			$json_response = curl_exec($curl);

			$response = json_decode($json_response);
			
			$this->_status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

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

						if ( isset($payout->platforms->platform) && $payout->platforms->platform && is_array($payout->platforms->platform) )
						{
							$os = $payout->platforms->platform;
						}
						else
						{
							$os = explode( ',' , $payout->platforms->platform );
						}				

						$oss 		 = [];
						$deviceTypes = [];

						foreach ( $os as $o )
						{
							switch ( strtolower($o) )
							{
								case 'ipad':
									if ( !in_array('iOS', $oss) )
										$oss[] 		   = 'iOS';

									if ( !in_array('Tablet', $deviceTypes) )
										$deviceTypes[] = 'Tablet';		
								break;
								case 'iphone':
									if ( !in_array('iOS', $oss) )
										$oss[] 		   = 'iOS';

									if ( !in_array('Smartphone', $deviceTypes) )
										$deviceTypes[] = 'Smartphone';
								break;
								case 'ipod':
									if ( !in_array('iOS', $oss) )
										$oss[] 		   = 'iOS';

									if ( !in_array('Other', $deviceTypes) )
										$deviceTypes[] = 'Other';
								break;
								case 'ios':
									if ( !in_array('iOS', $oss) )
										$oss[] 		   = 'iOS';
								break;
								case 'android':
									if ( !in_array($o, $oss) )
										$oss[]		   = 'Android';
								break;
								case 'windows phone':
									if ( !in_array('Windows', $oss) )
										$oss[] 		   = 'Windows';
								break;									
								default:
									if ( !in_array($o, $oss) )
										$oss[] 		   = $o;
								break;
							}
						}

						$result[] = [
							'ext_id' 			=> $payout->id,
							'name'				=> $campaign->offerName,
							'desc'				=> preg_replace('/[\xF0-\xF7].../s', '', $campaign->description),
							'payout' 			=> $payout->payout,
							'landing_url'		=> $campaign->targetURL,
							'country'			=> $countries,
							'device_type'		=> $deviceTypes,
							'connection_type'	=> null,
							'carrier'			=> null,
							'os'				=> $oss,
							'os_version'		=> null,
							'status'			=> 'active',
							'currency'			=> 'USD'
						];
					}
				}
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