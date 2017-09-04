<?php

	namespace backend\components;

	use Yii;
	use yii\base\Component;
	use app\models;
	use yii\base\InvalidConfigException;
	 
	// EL RESPONSE VIENE SIN OFERTAS
	class MinimobAPI extends Component
	{
		// uses hasoffers.com plattform
		const URL = 'http://dashboard.minimob.com/api/v1.1/myoffers/?';

		protected $_msg;
		protected $_status;

		public function requestCampaigns ( $api_key, $user_id = null  )
		{
			$url    = self::URL . 'apikey='.$api_key;
			$curl   = curl_init();

			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

			$response = json_decode(curl_exec($curl));
			$response = $response->response;

			$this->_status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

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

			$countries 	   = [];

			foreach ( $countryData->response->data AS $countryData )
			{
				$offerId = $countryData->offer_id;

				foreach ( $countryData->countries AS $code => $country )
				{
					$countries[$offerId][] = $code;	
				}				
			}

			$dbCarriers = models\Carriers::find()->all();

			foreach ( $response->data AS $ext_id => $offer )
			{
				$campaign = (array)$offer->Offer;

				if ( $campaign['approval_status'] != 'approved' )
					continue;

				// get tracking url data
				$url = 'https://pocketmedia.api.hasoffers.com/Apiv3/json?Target=Affiliate_Offer&Method=generateTrackingLink&offer_id='.$ext_id.'&api_key='.$api_key;

				curl_setopt($curl, CURLOPT_URL, $url);
				curl_setopt($curl, CURLOPT_HEADER, false);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl, CURLOPT_POST, 0);

				$urlData = json_decode(curl_exec($curl), true, 512, JSON_OBJECT_AS_ARRAY);

				if ( isset($urlData['response']['data']['click_url']) )
					$landingUrl = $urlData['response']['data']['click_url'];
				else
					$landingUrl = null;

				// get targeting data
				$url = 'https://pocketmedia.api.hasoffers.com/Apiv3/json?Target=Affiliate_OfferTargeting&Method=getRuleTargetingForOffer&offer_id='.$ext_id.'&api_key='.$api_key;

				curl_setopt($curl, CURLOPT_URL, $url);
				curl_setopt($curl, CURLOPT_HEADER, false);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);


				$targetResponse = json_decode(curl_exec($curl), true, 512, JSON_OBJECT_AS_ARRAY);

				$reqOs 		 = [];
				$reqOsVer 	 = [];
				$reqCarriers = [];

				$targetData = (array)$targetResponse;

				if ( $targetData['response']['data'] )
				{
					foreach ( $targetData['response']['data'] as $rule )
					{
						if ( $rule['action']!='allow' )
							continue;

						if ( $rule['rule']['req_mobile_carrier'] )
							$reqCarriers[]  = $rule['rule']['req_mobile_carrier'];

						if ( $rule['rule']['req_device_os'] )
							$reqOs[] 		= $rule['rule']['req_device_os'];

						if ( $rule['rule']['req_device_os_version'] )
							$reqOsVer[]     = $rule['rule']['req_device_os_version'];	
					}					
				}
			

				$os 	  	= ApiHelper::getOs($reqOs);
				$osVersions = ApiHelper::getValues($reqOsVer);
				$carriers 	= ApiHelper::getCarriers( $reqCarriers, $dbCarriers );

				switch ( strtolower($campaign['status']) )
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
					'name'				=> $campaign['name'],
					'desc'				=> preg_replace('/[\xF0-\xF7].../s', '', $campaign['description']),
					'payout' 			=> $campaign['default_payout'],
					'landing_url'		=> $landingUrl,
					'country'			=> isset($countries[$ext_id]) ? $countries[$ext_id] : null,
					'device_type'		=> null,
					'connection_type'	=> null,
					'carrier'			=> empty($carriers) ? null : $carriers,
					'os'				=> empty($os) ? null : $os, 
					'os_version'		=> empty($osVersions) ? null : $osVersions, 
					'status'			=> $status,
					'currency'			=> $campaign['currency']
				];

				unset( $campaign );
				unset( $reqOs );
				unset( $reqCarriers );
				unset( $os );
				unset( $reqOsVer );
				unset( $osVersions );
				unset( $carriers );
				unset( $urlData );
				unset( $targetData );
				unset( $targetResponse );
				
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