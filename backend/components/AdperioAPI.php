<?php

	namespace backend\components;

	use Yii;
	use yii\base\Component;
	use app\models;
	use yii\base\InvalidConfigException;
	 
	class AdperioAPI extends Component
	{
		// uses hasoffers.com plattform
		const URL = 'https://adperio.api.hasoffers.com/Apiv3/json?Target=Affiliate_Offer&Method=findMyApprovedOffers&contain[]=TrackingLink&contain[]=Country';

		protected $_msg;
		protected $_status;

		public function requestCampaigns ( $api_key, $user_id = null  )
		{
			$url    = self::URL . '&api_key='.$api_key;
			$curl   = curl_init();

			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

			$response = json_decode(curl_exec($curl));
			$response = $response->response;

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

			if ( isset($response->errorMessage) && $response->errorMessage )
			{
				$this->_msg = $response->errorMessage;				
				return false;
			}
			else if ( !isset($response->data) || !$response->data )
			{
				$this->_msg = 'No campaign data in response';
				return false;				
			}			

			$result = [];

			//$dbCarriers = models\Carriers::find()->all();

			foreach ( $response->data AS $ext_id => $campaign )
			{
				if ( $campaign->Offer->approval_status != 'approved' )
					continue;

				switch ( strtolower($campaign->Offer->status) )
				{
					case 'active':
						$status = 'active';
					break;
					default:
						$status = 'aff_paused';
					break;
				}				

				$country = [];
				foreach ( $campaign->Country as $code => $data )
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

				$os 	  	= ApiHelper::getOs($campaign->Offer->name, false);

				$devices 	= ApiHelper::getDeviceTypes($campaign->Offer->name, false);
				//$carriers 	= ApiHelper::getCarriers( $reqCarriers, $dbCarriers );
				$p 			= ApiHelper::getAppIdFromUrl( $campaign->Offer->preview_url );


				if ( $campaign->Offer->currency )
					$currency = $campaign->Offer->currency;
				else
					$currency = 'USD';


				$result[] = [	
					'ext_id' 			=> $ext_id,
					'name'				=> $campaign->Offer->name,
					'desc'				=> preg_replace('/[\xF0-\xF7].../s', '', $campaign->Offer->description),
					'payout' 			=> $campaign->Offer->default_payout,
					'landing_url'		=> $campaign->TrackingLink->click_url,
					'country'			=> empty($country) ? null : $country,
					'device_type'		=> empty($devices) ? null : $devices,
					'connection_type'	=> null,
					'carrier'			=> null,
					'os'				=> empty($os) ? null : $os, 
					'os_version'		=> null, 
					'package_id'		=> empty($p) ? null : $p,
					'status'			=> $status,
					'currency'			=> $currency
				];

				unset( $campaign );
				unset( $os );
				unset( $osVersions );
				unset( $country );
				unset( $devices );
				//unset( $carriers );
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