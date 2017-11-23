<?php

	namespace backend\components;

	use Yii;
	use yii\base\Component;
	use yii\base\InvalidConfigException;
	 
	class TestAPI extends Component
	{
		protected $_msg;
		protected $_status;

		public function requestCampaigns ( $api_key, $user_id = null  )
		{
			// ONLY FOR TESTING
			$result[] = [
				'ext_id' 			=> 'test_cp_id',
				'name'				=> 'test_cp',
				'desc'				=> 'this is a test', 
				'payout' 			=> 1,
				'landing_url'		=> 'http://google.com',
				'country'			=> ['AR'],
				'device_type'		=> ['Desktop'],
				'connection_type'	=> ['Carrier'],
				'carrier'			=> ['Movistar'],
				'os'				=> ['iOS'],
				'os_version'		=> ['10.1'],
				'package_id'		=> ['ios'=>"4444444"],
				'status'			=> 'active',
				'currency'			=> 'USD',
				'daily_cap'			=> 10
			];

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