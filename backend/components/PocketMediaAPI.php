<?php

	namespace backend\components;

	use Yii;
	use yii\base\Component;
	use yii\base\InvalidConfigException;
	 
	class PocketMediaAPI extends Component
	{
		// uses hasoffers.com plattform
		const URL = 'https://pocketmedia.api.hasoffers.com/Apiv3/json?&Target=Offer&Method=findAll';

		protected $_msg;
		protected $_status;

		public function requestCampaigns ( $api_key, $user_id = null  )
		{
			$url    = self::URL . '&NetworkToken='.$api_key;
			$curl   = curl_init($url);

			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

			$json_response = curl_exec($curl);

			$response = json_decode($json_response);

			$this->_status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

			if ( $response && isset( $response ) )
				$response = $response->response;
			else
				return false;

			if ( !$response )
			{
				return false;
			}
			else if ( isset($response->errorMessage) && $response->errorMessage )
			{
				$this->_msg = $response->errorMessage;				
				return false;
			}

			$result = [];

			foreach ( $response->data AS $ext_id => $campaign )
			{
				if ( isset($campaign->Countries) && $campaign->Countries && is_array($campaign->Countries) )
				{
					$countries = $campaign->Countries;
				}
				else
				{
					$countries = explode( ',' , $campaign->Countries );
				}

				if ( isset($campaign->Platforms) && $campaign->Platforms && is_array($campaign->Platforms) )
				{
					$os = $campaign->Platforms;
				}
				else
				{
					$os = explode( ',' , $campaign->Platforms );
				}				

				$oss 		 = [];
				$deviceTypes = [];

				foreach ( $os as $o )
				{
					switch ( $o )
					{
						case 'iPad':
							if ( !in_array('iOS', $oss) )
								$oss[] 		   = 'iOS';

							if ( !in_array('Tablet', $deviceTypes) )
								$deviceTypes[] = 'Tablet';							
						break;
						case 'iPhone':
							if ( !in_array('iOS', $oss) )
								$oss[] 		   = 'iOS';

							if ( !in_array('Smartphone', $deviceTypes) )
								$deviceTypes[] = 'Smartphone';
						break;
						case 'Android':
							if ( !in_array($o, $oss) )
								$oss[]		   = $o;

							if ( !in_array('Smartphone', $deviceTypes) )
								$deviceTypes[] = 'Smartphone';
						break;
						default:
							if ( !in_array($o, $oss) )
								$oss[] 		   = $o;

							if ( !in_array('Other', $deviceTypes) )
								$deviceTypes[] = 'Other'; 
						break;
					}
				}

				switch ( strtolower($campaign->status) )
				{
					case 'active':
						$status = 'active';
					break;
					default:
						$status = 'paused';
					break;
				}

				$result[] = [
					'ext_id' 			=> $ext_id,
					'name'				=> $campaign->Name,
					'desc'				=> $campaign->Description,
					'payout' 			=> $campaign->Payout,
					'landing_url'		=> $campaign->Tracking_url,
					'country'			=> $countries,
					'device_type'		=> $deviceTypes,
					'connection_type'	=> null,
					'carrier'			=> null,
					'os'				=> $oss,
					'os_version'		=> null,
					'status'			=> $status,
					'currency'			=> $campaign->Currency
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