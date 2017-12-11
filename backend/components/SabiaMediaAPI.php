<?php

	namespace backend\components;

	use Yii;
	use yii\base\Component;
	use app\models;
	use yii\base\InvalidConfigException;
	 
	class SabiaMediaAPI extends Component
	{
		// uses hasoffers.com plattform
		const URL = 'http://sabiamedia.afftrack.com/apiv2/?action=offer_feed';

		protected $_msg;
		protected $_status;

		public function requestCampaigns ( $api_key, $user_id = null  )
		{
			$url    = self::URL . '&key='.$api_key;

			if  ( isset($_GET['url']) && $_GET['url']==1 )
			{
				echo $url;
				die();
			}

			$curl   = curl_init();

			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

			$response = json_decode(curl_exec($curl));

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

			if ( !isset($response->offers) || empty($response->offers) )
			{
				$this->_msg = 'No campaign data in response';
				return false;				
			}			

			$result = [];


			foreach ( $response->offers AS $campaign )
			{			

				$country = [];
				foreach ( $campaign->countries as $data )
				{
					switch ( strtoupper($data->code) )
					{
						case 'UK':
							$country[] = 'GB';
						break;
						default:
							$country[] = strtoupper($data->code);
						break;
					}					
				}					

				$devices  = [];

				foreach ( $campaign->devices as $device )
				{
					$devices[] = $device->device_type;
				}

				$os 	  	= ApiHelper::getOs( $devices, false );
				$devices 	= ApiHelper::getDeviceTypes( $devices, false );
				$p 			= ApiHelper::getAppIdFromUrl( $campaign->preview_link );

				if ( isset($campaign->daily_cap) && $campaign->daily_cap>0 )
					$cap = $campaign->daily_cap;
				else
					$cap = null;

				$result[] = [	
					'ext_id' 			=> $campaign->id,
					'name'				=> $campaign->name,
					'desc'				=> preg_replace('/[\xF0-\xF7].../s', '', $campaign->description),
					'payout' 			=> $campaign->payout,
					'daily_cap'			=> $cap,
					'landing_url'		=> $campaign->tracking_link,
					'country'			=> empty($country) ? null : $country,
					'device_type'		=> empty($devices) ? null : $devices,
					'connection_type'	=> null,
					'carrier'			=> null,
					'os'				=> empty($os) ? null : $os, 
					'os_version'		=> null, 
					'package_id'		=> empty($p) ? null : $p,
					'status'			=> 'active',
					'currency'			=> 'USD'
				];

				unset( $campaign );
				unset( $os );
				//unset( $osVersions );
				unset( $country );
				unset( $devices );
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