<?php

	namespace backend\components;

	use Yii;
	use yii\base\Component;
	use app\models;
	use yii\base\InvalidConfigException;
	 
	class LeverageAPI extends Component
	{
		// uses hasoffers.com plattform
		const URL = '{Token}';

		protected $_msg;
		protected $_status;

		public function requestCampaigns ( $api_key, $user_id = null  )
		{
			$url    = 'http://leverage.echo226.com/2015-03-01/bulk?affiliate='.$user_id.'&auth='.$api_key;

			//$url .= '&applicationStatus=Approved';

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

			if ( !$response || !isset( $response->rows ) || empty($response->rows) )
			{
				$this->_msg = 'No campaign data in response';
				return false;
			}
		

			$result = [];

			foreach ( $response->rows AS $campaign )
			{
			
				$country = [];

				if ( isset( $campaign->countries ) )
				{
					foreach ( explode(',',$campaign->countries) as $code )
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
				}
				

				if ( isset( $campaign->os ) )
					$os 	  	= ApiHelper::getOs($campaign->os);
				else
					$os         = [];

				if ( isset( $campaign->deviceTypes ) )
					$device 	= ApiHelper::getDeviceTypes($campaign->deviceTypes);
				else
					$device     = [];

				$p 			= ApiHelper::getAppIdFromUrl( $campaign->previewUrl );

				if ( isset($campaign->trackingLink) )
				{
					$url = $campaign->trackingLink;
				}
				else
				{
					$url = null;
				}

				if ( isset($campaign->description) )
					$desc = preg_replace('/[\xF0-\xF7].../s', '', $campaign->description);
				else
					$desc = null;

				$result[] = [	
					'ext_id' 			=> $campaign->id,
					'name'				=> $campaign->name,
					'desc'				=> $desc,
					'payout' 			=> $campaign->payout,
					'landing_url'		=> $url,
					'country'			=> empty($country) ? null : $country,
					'device_type'		=> $device,
					'connection_type'	=> null,
					'carrier'			=> null,
					'os'				=> empty($os) ? null : $os, 
					'os_version'		=> null, 
					'package_id'		=> empty($p) ? null : $p,
					'status'			=> 'active',
					'currency'			=> $campaign->currency
				];

				unset( $campaign );
				unset( $os );
				unset( $osVersions );
				unset( $country );
				unset( $device );
				
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