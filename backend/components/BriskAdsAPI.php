<?php

	namespace backend\components;

	use Yii;
	use yii\base\Component;
	use yii\base\InvalidConfigException;
	 
	// STANDBY: VARIOS PAYOUTS SIN CRITERIO UNICO
	class BriskAdsAPI extends Component
	{
		// uses orangear.com plattform
		const URL = 'http://api.briskads.affise.com/3.0/offers?status[]=active';

		protected $_msg;
		protected $_status;

		public function requestCampaigns ( $api_key, $user_id = null  )
		{
			$url    = self::URL . '&API-key='.$api_key;
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
			else if ( !isset($response->offer) )
			{
				$this->_msg = 'No campaign data in response';
				return false;
			}

			$result = [];

			foreach ( $response->offers AS $campaign )
			{
				if ( $campaign->payments )
				{
					foreach ( $campaign->payments AS $payment )
					{
						$countries = $payment->countries;
						$os 	   = ApiHelper::getOs($payment->os);

						$result[] = [
							'ext_id' 			=> $payout->id,
							'name'				=> $campaign->offerName,
							'desc'				=> preg_replace('/[\xF0-\xF7].../s', '', $campaign->description),
							'payout' 			=> $payout->payout,
							'landing_url'		=> $campaign->targetURL,
							'country'			=> $countries,
							'device_type'		=> $deviceTypes,
							'connection_type'	=> empty($connectionType) ? null : $connectionType,
							'carrier'			=> null,
							'os'				=> $os,
							'os_version'		=> null,
							'status'			=> 'active',
							'currency'			=> 'USD'
						];

						unset ( $oss );
						unset ( $deviceTypes);
					}
				}
			}

			unset ( $connectionType );

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