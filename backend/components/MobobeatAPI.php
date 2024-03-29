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
			// ONLY FOR TESTING
			/*
			$result[] = [
				'ext_id' 			=> 'test_mobo_id',
				'name'				=> 'test_mobo 2',
				'desc'				=> 'bla bla', 
				'payout' 			=> 1,
				'landing_url'		=> 'http://google.com',
				'country'			=> ['AR'],
				'device_type'		=> ['Desktop'],
				'connection_type'	=> ['Carrier'],
				'carrier'			=> ['Movistar'],
				'os'				=> ['iOS'],
				'os_version'		=> ['10.1'],
				'package_id'		=> ['ios'=>"4444444"],
				'status'			=> 'active',
				'currency'			=> 'USD',
				'daily_cap'			=> 10
			];

			return $result;
			*/
		
			$url    = self::URL . '&apiKey='.$api_key;
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

				$deviceTypes = ApiHelper::getDeviceTypes($campaign->devices, false);
				$oss 		 = ApiHelper::getOs($campaign->devices, false);	

				if ( $campaign->preview_url )
				{
					$packageIds = ApiHelper::getAppIdFromUrl( $campaign->preview_url );

					if ( $campaign->packageid )
					{
						if ( isset($packageIds['android']) )
						{
							$packageIds['android'] = $campaign->packageid;
						}
						else if ( isset($packageIds['ios']) )
						{
							$packageIds['ios'] = ApiHelper::cleanAppleId($campaign->packageid);
						}
						else if ( !in_array( 'Android', $oss ) )
						{
							$packageIds['android'] = $campaign->packageid;
						}
						else if ( !in_array( 'iOS', $oss ) )
						{
							$packageIds['ios'] = ApiHelper::cleanAppleId($campaign->packageid);
						}
					}					
				}
				else if ( in_array( 'Android', $oss ) && $campaign->packageid )
				{
					$packageIds = [
						'android' => $campaign->packageid
					];
				}
				else if ( in_array( 'iOS', $oss ) && $campaign->packageid )
				{
					$packageIds = [
						'ios' => ApiHelper::cleanAppleId($campaign->packageid)
					];
				}				
				else
				{
					$packageIds = [];
				}						

				switch ( strtolower($campaign->status) )
				{
					case 'active':
						$status = 'active';
					break;
					default:
						$status = 'aff_paused';
					break;
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
					'ext_id' 			=> $campaign->id,
					'name'				=> $campaign->name,
					'desc'				=> preg_replace('/[\xF0-\xF7].../s', '', $campaign->description), 
					'payout' 			=> $campaign->payout,
					'currency'			=> 'USD',
					'cap'				=> $campaign->cap,
					'landing_url'		=> $campaign->tracking_url,
					'country'			=> $country,
					'device_type'		=> $deviceTypes,
					'connection_type'	=> null,
					'carrier'			=> null,
					'os'				=> empty($oss) ? null : $oss,
					'os_version'		=> null,
					'package_id'		=> empty($packageIds) ? null : $packageIds,
					'status'			=> $status,
				];

				unset ( $campaign );
				unset ( $country );
				unset ( $countries );
				unset ( $deviceTypes);
				unset ( $os );
				unset ( $packageIds );
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