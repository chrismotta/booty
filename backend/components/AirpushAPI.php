<?php

	namespace backend\components;

	use Yii;
	use yii\base\Component;
	use yii\base\InvalidConfigException;
	 
	class AirpushAPI extends Component
	{
		const URL = 'http://api.airpush.affise.com/3.0/partner/offers';

		protected $_msg;
		protected $_status;

		public function requestCampaigns ( $api_key, $user_id = null  )
		{
			// ONLY FOR TESTING
			/*
			$result[] = [
				'ext_id' 			=> 'test_apush_id',
				'name'				=> 'test_apush',
				'desc'				=> 'bla bla', 
				'payout' 			=> 1,
				'landing_url'		=> 'http://google.com',
				'country'			=> ['US'],
				'device_type'		=> ['tablet'],
				'connection_type'	=> ['WiFi'],
				'carrier'			=> null,
				'os'				=> ['iOS'],
				'os_version'		=> null,
				'package_id'		=> ['ios'=>"4444444"],
				'status'			=> 'active',
				'currency'			=> 'USD'
			];

			return $result;
			*/
			
			$url  = self::URL.'?API-key='.$api_key;
			$curl = curl_init();

			curl_setopt($curl, CURLOPT_URL, $url );
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

			$json_response = curl_exec($curl);

			if  ( isset($_GET['raw']) && $_GET['raw']==1 )
			{
				header('Content-Type: text/json');
				echo json_encode( $json_response, JSON_PRETTY_PRINT );
				die();
			}

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
			else if ( !isset($response->offers[0]->id) )
			{
				$this->_msg = 'No campaign data in response';
				return false;
			}
			else
			{
				$next = true;
			}

			$result = [];

			while ( $next )
			{
				foreach ( $response->offers AS $campaign )
				{
					if ( isset($campaign->countries[0]) )
					{
						foreach ( $campaign->countries as $code )
						{
							$countries[] = strtoupper($code);
						}
					}
					else
					{
						$countries = [];
					}			
							
					if ( $campaign->preview_url )
					{
						$packageIds = ApiHelper::getAppIdFromUrl( $campaign->preview_url );
					}
					else
					{
						$packageIds = [];
					}

					switch ( $campaign->required_approval )
					{
						case 1:
						case true:
							$status = 'aff_paused';
						break;
						default:
							$status = 'active';
						break;
					}


					foreach ( $campaign->payments as $payment )
					{
						$onlyOnePayment = false;

						if ( isset($payment->countries[0]) )
							$countries = $payment->countries;
						else
							$onlyOnePayment = true;


						$deviceTypes = ApiHelper::getDeviceTypes($payment->devices, false);
						$oss 		 = ApiHelper::getOs($payment->os, false);				

						$country = [];

						foreach  ( $countries as $code )
						{
							switch ( strtoupper($code) )
							{
								case 'UK':
									$country[] = 'GB';
								break;
								default:
									$country[] = strtoupper($code);
								break;
							}
						}

						if ( $onlyOnePayment )
							$id = $campaign->id;
						else
							$id = $campaign->id . ':'.$country[0];


						if ( $campaign->cap && $campaign->cap>0 )
							$cap = $campaign->cap;
						else
							$cap = null; 

						$result[] = [	
							'ext_id' 			=> $id,
							'name'				=> $campaign->title,
							'desc'				=> preg_replace('/[\xF0-\xF7].../s', '', $campaign->description), 
							'payout' 			=> $payment->revenue,
							'daily_cap'			=> $cap,
							'landing_url'		=> $campaign->link,
							'country'			=> $country,
							'device_type'		=> $deviceTypes,
							'connection_type'	=> null,
							'carrier'			=> null,
							'os'				=> empty($oss) ? null : $oss,
							'os_version'		=> null,
							'package_id'		=> empty($packageIds) ? null : $packageIds,
							'status'			=> $status,
							'currency'			=> strtoupper($payment->currency)
						];

						if ( $onlyOnePayment )
							break;

						unset ( $country );
						unset ( $countries );
						unset ( $deviceTypes);
						unset ( $os );
						unset ( $packageIds );

					}
				}

				if ( isset($response->pagination->next_page) && $response->pagination->next_page )
				{
					$url = self::URL . '?API-key='.$api_key.'&page='.$response->pagination->next_page;
					curl_setopt($curl, CURLOPT_URL, $url );
					
					$json_response = curl_exec($curl);
					$response = json_decode($json_response);

					if ( $response && isset($response->offers[0]->id) )
						$next = true;
					else
						$next = false;									
				}
				else
				{
					$next = false;
				}				
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