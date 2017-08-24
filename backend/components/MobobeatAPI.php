<?php

	namespace backend\components;

	use Yii;
	use yii\base\Component;
	use yii\base\InvalidConfigException;
	 
	class MobobeatAPI extends Component
	{
		// uses orangear.com plattform
		const URL = 'https://cpa.mobobeat.com/api/getOffers.php?format=json';

		protected $_msg;
		protected $_status;

		public function requestCampaigns ( $api_key, $user_id = null  )
		{
			$url    = self::URL . '&apiKey='.$api_key;
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

			$result = [];

			foreach ( $response->data AS $data )
			{
				$campaign = $data->campaign;

				if ( isset($campaign->countries) && $campaign->countries && is_array($campaign->countries) )
				{
					$countries = $campaign->countries;
				}
				else
				{
					$countries = explode( ',' , $campaign->countries );
				}

				if ( isset($campaign->devices) && $campaign->devices && is_array($campaign->devices) )
				{
					$os = $campaign->devices;
				}
				else
				{
					$os = explode( ',' , $campaign->devices );
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


				$result[] = [
					'ext_id' 			=> $campaign->id,
					'name'				=> $campaign->name,
					'desc'				=> $campaign->description,		
					'payout' 			=> $campaign->payout,
					'landing_url'		=> $campaign->tracking_url,
					'country'			=> $countries,
					'device_type'		=> $deviceTypes,
					'connection_type'	=> null,
					'carrier'			=> null,
					'os'				=> $oss,
					'os_version'		=> null,
					'status'			=> strtolower($campaign->status),
					'currency'			=> 'USD'
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