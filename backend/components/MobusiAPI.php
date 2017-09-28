<?php

	namespace backend\components;

	use Yii;
	use yii\base\Component;
	use app\models;
	use yii\base\InvalidConfigException;
	 
	class MobusiAPI extends Component
	{
		const URL = 'http://api.leadzu.com/offer.find?';

		protected $_msg;
		protected $_status;

		public function requestCampaigns ( $api_key, $user_id = null  )
		{
			$url    = self::URL;
			$curl   = curl_init();

			$fields = array(
				'user_id' => $user_id,
				'api_key' => $api_key,
				'approved' => true
			);

 			$data_string = json_encode($fields);

			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");;
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);			

			$response = json_decode(curl_exec($curl));

			$this->_status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

			if  ( isset($_GET['source']) && $_GET['source']==1 )
			{
				header('Content-Type: text/json');
				echo json_encode( $response, JSON_PRETTY_PRINT );
				die();
			}

			if ( !$response || !isset($response->answer) )
			{
				$this->_msg = 'Response without body';
				return false;
			}

			$dbCarriers = models\Carriers::find()->all();

			foreach ( $response->answer AS $ext_id => $campaign )
			{
				$os        = [];
				$osVer     = [];
				$devices   = [];
				$connTypes = [];

				switch ( strtolower($campaign->status) )
				{
					case 'active':
						$status = 'active';
					break;
					default:
						$status = 'aff_paused';
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
				
				if ( isset($campaign->os_version) )
				{
					foreach ( $campaign->os_version AS $os => $versions )
					{
						if ( isset($versions->gt) ) 
							$osVer[] 	= $versions->gt;

						if ( isset($versions->ge) ) 
							$osVer[] 	= $versions->ge;

						if ( isset($versions->eq) ) 
							$osVer[] 	= $versions->eq;
		 			}					
				}

	 			if ( isset($campaign->os->allowed) )
	 				$o = ApiHelper::getOs( $campaign->os->allowed, false );

	 			$v = ApiHelper::getValues( $osVer );
				$p = ApiHelper::getAppIdFromUrl($campaign->preview);


	 			foreach ( $campaign->countries AS $code => $data )
	 			{
	 				$carriers = [];

	 				if ( isset( $data->carriers->allowed ) && $data->carriers->allowed )
	 				{
	 					if ( !in_array('Carrier', $connTypes) )
	 						$connTypes[] = 'Carrier';

	 					foreach ( $data->carriers->allowed as $carrier )
	 					{
	 						$carriers[] = $carrier->name;
	 					}
	 				}

	 				if ( isset( $data->carriers->wifi ) && ( $data->carriers->wifi!=false || $data->carriers->wifi!=0 )  && !in_array('WiFi', $connTypes) )
	 				{
	 					$connTypes[] = 'WiFi';
	 				}

					$result[] = [
						'ext_id' 			=> $campaign->id.':'.$code, 
						'name'				=> $campaign->title, 
						'desc'				=> preg_replace('/[\xF0-\xF7].../s', '', $campaign->description),
						'payout' 			=> isset( $data ) ? (float)$data->payout : null, 
						'landing_url'		=> $data->url, 
						'country'			=> isset( $code ) ? [ $code ] : null,
						'device_type'		=> empty($devices) ? null : $devices,
						'connection_type'	=> empty($connTypes) ? null : $connTypes, 
						'carrier'			=> empty($carriers) ? null : $carriers,
						'os'				=> empty($o) ? null : $o, 
						'os_version'		=> empty($v) ? null : $v, 
						'package_id'		=> empty($p) ? null : $p,
						'status'			=> $status, 
						'currency'			=> $data->payoutCurrency
					];	 				

					unset ( $carriers );
	 			}	 			

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