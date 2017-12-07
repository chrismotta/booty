<?php

	namespace backend\components;

	use Yii;
	use yii\base\Component;
	use app\models;
	use yii\base\InvalidConfigException;

	class CurateMobileAPI extends Component
	{
		const URL = 'https://api.curatemobile.com:8080';

		protected $_msg;
		protected $_status;

		public function requestCampaigns ( $api_key, $user_id = null  )
		{
			$url    = self::URL . '/v1/api/approved_offers?p=1240&page=1';
			$curl   = curl_init();

			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HTTPHEADER, [
				'Accept: application/json',
				'Authorization: Token token='.$api_key
			]);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

			$raw = curl_exec($curl);

			if  ( isset($_GET['raw']) && $_GET['raw']==1 )
			{
				header('Content-Type: text/plain');
				echo $raw;
				die();
			}

			$response = json_decode($raw);

			$this->_status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

			if  ( isset($_GET['source']) && $_GET['source']==1 )
			{
				header('Content-Type: text/json');
				echo json_encode( $response, JSON_PRETTY_PRINT );
				die();
			}

			if ( !$response || !isset( $response ) )
			{
				$this->_msg = 'Response without body';
				return false;
			}
			else if ( !isset($response->data) || empty($response->data) )
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
				foreach ( $response->data->offers AS $campaign )
				{
					switch ( strtolower($campaign->status) )
					{
						case 'active':
							$status = 'active';
						break;
						default:
							$status = 'aff_paused';
						break;
					}				

					$country    = [];
					$plattforms = [];
					$connTypes  = [];

					foreach ( $campaign->targeting_rules as $rule )
					{
						if ( $rule->logic=='allow' && $rule->type == 'Country' )
						{
							switch ( strtoupper($rule->value) )
							{
								case 'UK':
									$country[] = 'GB';
								break;
								default:
									$country[] = strtoupper($rule->value);
								break;
							}											
						}

						if ( $rule->logic=='allow' && $rule->type == 'OperatingSystem' )
						{
							$plattforms[] = $rule->value;

							switch ( strtolower($rule->platform) )
							{
								case 'mobile':
									$connTypes[] = 'mobile';
								break;
							}
						}
					}					

					$os 	  	= ApiHelper::getOs($plattforms);
					$devices 	= ApiHelper::getDeviceTypes($plattforms);
					$p 			= ApiHelper::getAppIdFromUrl( $campaign->preview_url );

					if ( $campaign->cap_type=='daily' )
						$cap = $campaign->cap_value;
					else
						$cap = null;


					$result[] = [	
						'ext_id' 			=> $campaign->id,
						'name'				=> $campaign->name,
						'desc'				=> preg_replace('/[\xF0-\xF7].../s', '', $campaign->description),
						'payout' 			=> (float)preg_replace('/[$]/', '', $campaign->payout_value),
						'daily_cap'			=> $cap,
						'landing_url'		=> $campaign->tracking_url,
						'country'			=> empty($country) ? null : $country,
						'device_type'		=> empty($devices) ? null : $devices,
						'connection_type'	=> empty($connTypes) ? null : $connTypes,
						'carrier'			=> null,
						'os'				=> empty($os) ? null : $os, 
						'os_version'		=> null, 
						'package_id'		=> empty($p) ? null : $p,
						'status'			=> $status,
						'currency'			=> 'USD'
					];

					unset( $campaign );
					unset( $os );
					unset( $plattforms );
					unset( $country );
					unset( $p );
					unset( $devices );
					unset( $connTypes );
				}

				if ( isset($response->pagination->next_link) && $response->pagination->next_link )
				{
					curl_setopt($curl, CURLOPT_URL, self::URL . $response->pagination->next_link );

					$json_response = curl_exec($curl);

					$response = json_decode($json_response);

					if ( $response && isset($response->data->offers[0]->id) )
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