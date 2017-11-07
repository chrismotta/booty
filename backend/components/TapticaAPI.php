<?php

	namespace backend\components;

	use Yii;
	use yii\base\Component;
	use app\models;
	use yii\base\InvalidConfigException;
	 
	class TapticaAPI extends Component
	{
		const URL = 'https://api.taptica.com/v2/bulk?format=json&version=2';

		protected $_msg;
		protected $_status;

		public function requestCampaigns ( $api_key, $user_id = null  )
		{
			$curl   = curl_init();

			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);		

			curl_setopt($curl, CURLOPT_URL, self::URL . '&token='.$api_key.'&platforms=iPhone');
			$response1 = json_decode(curl_exec($curl));

			curl_setopt($curl, CURLOPT_URL, self::URL . '&token='.$api_key.'&platforms=Android');
			$response2 = json_decode(curl_exec($curl));

			curl_setopt($curl, CURLOPT_URL, self::URL . '&token='.$api_key.'&platforms=iPod');
			$response3 = json_decode(curl_exec($curl));

			curl_setopt($curl, CURLOPT_URL, self::URL . '&token='.$api_key.'&platforms=iPad');
			$response4 = json_decode(curl_exec($curl));

			$response = array_merge( $response1->Data, $response2->Data, $response3->Data, $response4->Data );

			$this->_status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

			if  ( isset($_GET['source']) && $_GET['source']==1 )
			{
				header('Content-Type: text/json');
				echo json_encode( $response, JSON_PRETTY_PRINT );
				die();
			}

			if ( !$response || empty($response) )
			{
				$this->_msg = 'Response without body';
				return false;
			}

			$dbCarriers = models\Carriers::find()->all();

			foreach ( $response AS $campaign )
			{
				$connTypes = [];
				$osVer 	   = [];

				if ( isset($campaign->Networks) )
				{
					if ( in_array( '3G', $campaign->Networks ) )
						$connTypes[] = 'Carrier';

					if ( in_array( 'WIFI', $campaign->Networks ) )
						$connTypes[] = 'WiFi';
				}

	 			if ( isset($campaign->Platforms) )
	 			{
	 				$os 	 = ApiHelper::getOs( $campaign->Platforms, false );
	 				$devices = ApiHelper::getDeviceTypes( $campaign->Platforms, false );
	 			}
	 			else
	 			{
					$os      = [];
					$devices = [];	 				
	 			}

	 			if ( isset( $campaign->MinOsVersion ) )
	 				$osVer[] = $campaign->MinOsVersion;

				if ( isset($campaign->PreviewLink) )
				{
					$packageIds = ApiHelper::getAppIdFromUrl( $campaign->PreviewLink );

					if ( isset($campaign->MarketAppId) )
					{						
						if ( isset($packageIds['android']) )
						{
							$packageIds['android'] = $campaign->MarketAppId;
						}
						else if ( isset($packageIds['ios']) )
						{
							$packageIds['ios'] = ApiHelper::cleanAppleId($campaign->MarketAppId);
						}
						else if ( !in_array( 'Android', $os ) )
						{
							$packageIds['android'] = $campaign->MarketAppId;
						}
						else if ( !in_array( 'iOS', $os ) )
						{
							$packageIds['ios'] = ApiHelper::cleanAppleId($campaign->MarketAppId);
						}
					}					
				}
				else if ( in_array( 'Android', $os ) && $campaign->MarketAppId )
				{
					$packageIds = [
						'android' => $campaign->MarketAppId
					];
				}
				else if ( in_array( 'iOS', $os ) && $campaign->MarketAppId )
				{
					$packageIds = [
						'ios' => ApiHelper::cleanAppleId($campaign->MarketAppId)
					];
				}				
				else
				{
					$packageIds = [];
				}

				if ( isset($campaign->SupportedCountriesV2) )
				{
		 			foreach ( $campaign->SupportedCountriesV2 AS $data )
		 			{
		 				if ( !isset($data->country, $countries) )
		 					$country = [ $data->country ];
		 				else
		 					$country = null;

		 				// parse carriers
			 			foreach ( $campaign->Carriers AS $carrierData )
			 			{
			 				$carriers = [];

			 				if ( isset( $carrierData->Carriers ) && is_array($carrierData->Carriers) )
			 				{
			 					if ( !in_array('Carrier', $connTypes) )
			 						$connTypes[] = 'Carrier';

			 					foreach ( $carrierData->Carriers as $carrier )
			 					{
			 						if ( !in_array( $carrier, $carriers ) )
			 							$carriers[] = $carrier;
			 					}
			 				}
			 			}		 				

						$result[] = [
							'ext_id' 			=> $campaign->OfferId.':'.$data->country, 
							'name'				=> $campaign->Name, 
							'desc'				=> preg_replace('/[\xF0-\xF7].../s', '', $campaign->Description),
							'payout' 			=> (float)$campaign->Payout, 
							'landing_url'		=> $campaign->TrackingLink, 
							'country'			=> $country,
							'device_type'		=> empty($devices) ? null : $devices,
							'connection_type'	=> empty($connTypes) ? null : $connTypes, 
							'carrier'			=> empty($carriers) ? null : $carriers,
							'os'				=> empty($os) ? null : $os, 
							'os_version'		=> empty($osVer) ? null : $osVer, 
							'package_id'		=> empty($packageIds) ? null : $packageIds,
							'status'			=> 'active', 
							'currency'			=> 'USD'
						];	 				

						unset ( $carriers );
						unset ( $country);
		 			}
				}
				else
				{
					$result[] = [
						'ext_id' 			=> $campaign->OfferId.':'.$data->country, 
						'name'				=> $campaign->Name, 
						'desc'				=> preg_replace('/[\xF0-\xF7].../s', '', $campaign->Description),
						'payout' 			=> (float)$campaign->Payout, 
						'landing_url'		=> $campaign->TrackingLink, 
						'country'			=> null,
						'device_type'		=> empty($devices) ? null : $devices,
						'connection_type'	=> empty($connTypes) ? null : $connTypes, 
						'carrier'			=> null,
						'os'				=> empty($os) ? null : $os, 
						'os_version'		=> empty($osVer) ? null : $osVer, 
						'package_id'		=> empty($packageIds) ? null : $packageIds,
						'status'			=> 'active', 
						'currency'			=> 'USD'
					];	 						
				}

				unset ( $packageIds );
				unset( $campaign );
				unset( $os );
				unset( $osVer );
				unset( $devices );
				unset( $connTypes );				
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