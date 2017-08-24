<?php

	namespace backend\components;

	use Yii;
	use yii\base\Component;
	use yii\base\InvalidConfigException;
	 
	class GlispaAPI extends Component
	{
		// uses orangear.com plattform
		const URL = 'http://feed.platform.glispa.com/native-feed/';

		protected $_msg;
		protected $_status;

		public function requestCampaigns ( $api_key, $user_id = null  )
		{
			$url    = self::URL . $api_key . '/app';
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
			else if ( !isset($response->data) )
			{
				$this->_msg = 'No campaign data in response';
				return false;
			}

			$creatives = (array) $response->data[1]->creatives;

			// debug response
			// var_dump($creatives['480x75']);die();

			$result = [];

			foreach ( $response->data AS $data )
			{
				$campaign = $data;//disambiguation

				if ( isset($campaign->countries) && $campaign->countries && is_array($campaign->countries) )
				{
					$countries = $campaign->countries;
				}
				else
				{
					$countries = explode( ',' , $campaign->countries );
				}

				if ( isset($campaign->mobile_platform) && $campaign->mobile_platform && is_array($campaign->mobile_platform) )
				{
					$os = $campaign->mobile_platform;
				}
				else
				{
					$os = explode( ',' , $campaign->mobile_platform );
				}

				if ( isset($campaign->mobile_min_version) && $campaign->mobile_min_version && is_array($campaign->mobile_min_version) )
				{
					$os_version = $campaign->mobile_min_version;
				}
				else
				{
					$os_version = explode( ',' , $campaign->mobile_min_version );
				}	

				/*
				if ( isset($campaign->mobile_operators) && $campaign->mobile_operators && is_array($campaign->mobile_operators) )
				{
					$carrier = $campaign->mobile_operators;
				}
				else
				{
					$carrier = explode( ',' , $campaign->mobile_operators );
				}				
				*/

				// $oss 		 = [];
				$deviceTypes = [];

				/* 
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
						case 'ios':
							if ( !in_array('iOS', $oss) )
								$oss[] 		   = 'iOS';

						break;
						case 'android':
							if ( !in_array($o, $oss) )
								$oss[]		   = 'Android';
						break;
						default:
							if ( !in_array($o, $oss) )
								$oss[] 		   = $o;

							if ( !in_array('Other', $deviceTypes) )
								$deviceTypes[] = 'Other'; 
						break;
					}
				}
				*/

				$result[] = [
					'ext_id' 			=> $campaign->campaign_id,
					'name'				=> $campaign->name,
					'desc'				=> preg_replace('/[\xF0-\xF7].../s', '', $campaign->description), //extract utf8mb4 characters
					'payout' 			=> $campaign->payout_amount,
					'landing_url'		=> $campaign->click_url,
					'country'			=> $countries,
					'device_type'		=> null,
					'connection_type'	=> null,
					'carrier'			=> null,
					'os'				=> $os,
					'os_version'		=> $os_version,
					'status'			=> 'active',
					'currency'			=> 'USD',
					'creative_320x50'	=> $creatives['320x50'],
				];
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