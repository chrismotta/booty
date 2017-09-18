<?php

	namespace backend\components;

	use Yii;
	use yii\base\Component;
	use app\models;
	use yii\base\InvalidConfigException;
	 
	// no mandan package id
	class AddictiveAdsAPI extends Component
	{
		// uses hasoffers.com plattform
		const URL = 'http://feed.addictiveads.com/v1?';

		protected $_msg;
		protected $_status;

		public function requestCampaigns ( $api_key, $user_id = null  )
		{
			$url    = self::URL . 'api_key='.$api_key;
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

			if ( !$response || !isset($response->data) || !is_array($response->data))
			{
				$this->_msg = 'Response without body';
				return false;
			}

			$dbCarriers = models\Carriers::find()->all();

			foreach ( $response->data AS $campaign )
			{
				$os       = [];
				$osVer    = [];
				$carriers = [];
				$devices  = [];

				foreach ( $campaign->targeting AS $rule )
				{
					if ( $rule->action=='allow' )
					{
						$os[] 	    = $rule->rule->req_device_os; 
						$osVer[] 	= $rule->rule->req_device_os_version; 
						$carriers[] = $rule->rule->req_mobile_carrier;
						$devices[]  = $rule->rule->req_device_model;		
					}
	 			}

	 			$d = ApiHelper::getDeviceTypes( $devices );
	 			$o = ApiHelper::getOs( $os );
	 			$c = ApiHelper::getCarriers( $carriers, $dbCarriers );
	 			$v = ApiHelper::getValues( $osVer );
	 			$p = ApiHelper::getAppIdFromUrl( $campaign->preview_url );

				$result[] = [
					'ext_id' 			=> $campaign->id, 
					'name'				=> $campaign->name, 
					'desc'				=> preg_replace('/[\xF0-\xF7].../s', '', $campaign->description), 
					'payout' 			=> (float)$campaign->payout, 
					'landing_url'		=> $campaign->tracking_url, 
					'country'			=> empty($campaign->geos) ? null : $campaign->geos,
					'device_type'		=> empty($d) ? null : $d,
					'connection_type'	=> null, 
					'carrier'			=> empty($c) ? null : $c,
					'os'				=> empty($o) ? null : $o, 
					'os_version'		=> empty($v) ? null : $v, 
					'package_id'		=> empty($p) ? null : $p,
					'status'			=> 'active', 
					'currency'			=> 'USD'
				];

				unset( $campaign );
				unset( $os );
				unset( $osVer );
				unset( $carriers );
				unset( $devices );				
				unset( $d );
				unset( $o );
				unset( $c );
				unset( $v );
				unset( $p );				
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