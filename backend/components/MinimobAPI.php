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

			$this->_status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

			if ( !$response )
			{
				$this->_msg = 'Response without body';
				return false;
			}
			else if ( !isset($response->offers) )
			{
				$this->_msg = 'No campaign data in response';
				return false;
			}		

			$result = [];

			foreach ( $response->offers AS $campaign )
			{
				$countries 	   = [];

				foreach ( $campaign->targetedCountries AS $countryData )
				{
					$cd = (array)$countryData;

					if ( !in_array( $cd['countryCode'], $countries) )
						$countries[] = $cd['countryCode'];
				}

				$os 	  	= ApiHelper::getOs($campaign->targetPlatform);

				switch ( strtolower($campaign->runningStatus) )
				{
					case 'running':
						$status = 'active';
					break;					
					default:
						$status = 'paused';
					break;
				}

				$result[] = [
					'ext_id' 			=> $campaign->id,
					'name'				=> $campaign->name,
					'desc'				=> preg_replace('/[\xF0-\xF7].../s', '', $campaign->description),
					'payout' 			=> $campaign->payout,
					'landing_url'		=> $campaign->objectiveUrl,
					'country'			=> empty($countries) ? null : $countries,
					'device_type'		=> null,
					'connection_type'	=> null,
					'carrier'			=> null,
					'os'				=> empty($os) ? null : $os, 
					'os_version'		=> null, 
					'status'			=> $status,
					'currency'			=> $campaign->payoutCurrency
				];

				unset( $campaign );
				unset( $os );
				unset( $countries );
				
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