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

			if  ( isset($_GET['source']) && $_GET['source']==1 )
			{
				header('Content-Type: text/json');
				echo json_encode( $response, JSON_PRETTY_PRINT );
				die();
			}				
			
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
	
				$os_version  = ApiHelper::getValues($campaign->mobile_min_version);
				$os 		 = ApiHelper::getOs($campaign->mobile_platform);
				$deviceTypes = ApiHelper::getDeviceTypes($campaign->mobile_platform, false);
		

				if ( in_array( 'Android', $os ) && $campaign->mobile_app_id )
				{
					$packageIds = [
						'android' => $campaign->mobile_app_id
					];
				}
				else if ( in_array( 'iOS', $os ) && $campaign->mobile_app_id )
				{
					$packageIds = [
						'ios' => ApiHelper::cleanAppleId($campaign->mobile_app_id)
					];
				}				
				else if ( $campaign->mobile_app_id )
				{
					$packageIds = [
						strtolower($os[0]) => $campaign->mobile_app_id
					];
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
					'ext_id' 			=> $campaign->campaign_id,
					'name'				=> $campaign->name,
					'desc'				=> preg_replace('/[\xF0-\xF7].../s', '', $campaign->description), //extract utf8mb4 characters
					'payout' 			=> $campaign->payout_amount,
					'currency'			=> 'USD',
					//'daily_cap'			=> $campaign->daily_remaining_leads,
					'landing_url'		=> $campaign->click_url,
					'country'			=> $country,
					'device_type'		=> null,
					'connection_type'	=> null,
					'carrier'			=> null,
					'os'				=> $os,
					'os_version'		=> $os_version,
					'package_id'		=> empty($packageIds) ? null : $packageIds,
					'status'			=> 'active',
					'creative_320x50'	=> $creatives['320x50'],
				];

				unset ( $countries );
				unset ( $packageIds );
				unset ( $country );				
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