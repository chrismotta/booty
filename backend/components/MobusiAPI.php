<?php

	namespace backend\components;

	use Yii;
	use yii\base\Component;
	use app\models;
	use yii\base\InvalidConfigException;
	 
	class MobusiAPI extends Component
	{
		// uses hasoffers.com plattform
		const URL = 'http://api.leadzu.com/offer.find?';

		protected $_msg;
		protected $_status;

		public function requestCampaigns ( $api_key, $user_id = null  )
		{
			$url    = self::URL . 'user_id='.$user_id.'&api_key='.$api_key;
			$curl   = curl_init();

			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

			$response = json_decode(curl_exec($curl));

			$this->_status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

			if ( !$response || !isset($response->data) || !is_array($response->data))
			{
				$this->_msg = 'Response without body';
				return false;
			}

			$dbCarriers = models\Carriers::find()->all();

			foreach ( $response->answer AS $ext_id => $campaign )
			{
				$os        = [];
				$osVer     = [];
				$carriers  = [];
				$devices   = [];
				$connTypes = [];

				switch ( strtolower($campaign->status) )
				{
					case 'active':
						$status = 'active';
					break;
					default:
						$status = 'paused';
					break;
				}
				
				switch( strtolower($campaign->device) )		
				{
					case 'mobile':
						$connTypes[] = 'Carrier';
					break;
					case 'desktop':
						$devices[]   = 'Desktop';
					break;
				}

				foreach ( $campaign->os_version AS $os => $versions )
				{
					if ( $rule->action=='allow' )
					{
						$os[] 	    = $os;

						if ( isset($versions->gt) ) 
							$osVer[] 	= $versions->gt;

						if ( isset($versions->ge) ) 
							$osVer[] 	= $versions->ge;

						if ( isset($versions->eq) ) 
							$osVer[] 	= $versions->eq;
					}
	 			}

	 			$o = ApiHelper::getOs( $os );
	 			$v = ApiHelper::getValues( $osVer );

				$result[] = [
					'ext_id' 			=> $campaign->id, 
					'name'				=> $campaign->title, 
					'desc'				=> preg_replace('/[\xF0-\xF7].../s', '', $campaign->description),
					'payout' 			=> (float)$campaign->payout, 
					'landing_url'		=> $campaign->tracking_url, 
					'country'			=> $country,
					'device_type'		=> empty($devices) ? null : $devices,
					'connection_type'	=> empty($connTypes) ? null : $connTypes, 
					'carrier'			=> null,
					'os'				=> empty($o) ? null : $o, 
					'os_version'		=> empty($v) ? null : $v, 
					'status'			=> $status, 
					'currency'			=> 'USD'
				];

				unset( $campaign );
				unset( $os );
				unset( $osVer );
				unset( $carriers );
				unset( $devices );				
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