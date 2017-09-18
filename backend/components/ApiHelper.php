<?php

	namespace backend\components;
	 
	 
	use Yii;
	use yii\base\Component;
	use yii\base\InvalidConfigException;
	 
	class ApiHelper extends Component
	{
	
		static function getPlatformsData ( $data )
		{
			if ( is_array($data) )
				$platforms = $data;
			else		
				$platforms = explode( ',' , $data );


			$result = [
				'os'			=> [],
				'device_type'	=> []
			];

			foreach ( $platforms as $platform )
			{
				switch ( strtolower($platform) )
				{
					case 'iphone':
						if ( !in_array('iOS', $result['os']) )
							$result['os'][] 		 = 'iOS';

						if ( !in_array('Smartphone', $result['device_type']) )
							$result['device_type'][] = 'Smartphone';
					break;
					case 'android':
						if ( !in_array($platform, $result['os']) )
							$result['os'][]		   	 = $platform;
					break;		
					case 'ios':
						if ( !in_array('iOS', $result['os']) )
							$result['os'][] 		 = 'iOS';
					break;								
					case 'ipad':
						if ( !in_array('iOS', $result['os']) )
							$result['os'][] 		 = 'iOS';

						if ( !in_array('Tablet', $result['device_type']) )
							$result['device_type'][] = 'Tablet';							
					break;					
					case 'windows phone':
						if ( !in_array('Windows', $result['os']) )
							$result['os'][] 		 = 'Windows';
					break;
					case 'ipod':
						if ( !in_array('iOS', $result['os']) )
							$result['os'][] 		 = 'iOS';

						if ( !in_array('Other', $result['device_type']) )
							$result['device_type'][] = 'Other';
					break;											
					default:
						if ( !in_array($platform, $result['os']) )
							$result['os'][] 		 = $platform;
					break;
				}
			}

			return $result;
		}


		static function getOs ( $data, $otherAsDefault = true )
		{
			$results = [];


			if ( is_array($data) )
				$values = $data;
			else
				$values = explode( ',' , $data );	


			foreach ( $values as $v )
			{
				$platforms = preg_split( '/[^a-zA-Z\d]/', $v, null, PREG_SPLIT_NO_EMPTY );

				foreach ( $platforms as $p )
				{
					switch ( strtolower($p) )
					{
						case 'ios':
							if ( !in_array( 'iOS', $results) )
								$results[] = 'iOS';
						break;
						case 'android':
							if ( !in_array( 'Android', $results) )
								$results[] = 'Android';						
						break;
						case 'windows':
							if ( !in_array( 'Windows', $results) )
								$results[] = 'Windows';						
						break;
						case 'blackberry':
							if ( !in_array( 'BlackBerry', $results) )
								$results[] = 'BlackBerry';							
						break;
						case null:
						break;
						default:
							if ( $otherAsDefault && !in_array( 'Other', $results) )
								$results[] = 'Other';
						break;
					}
				}

				unset ( $platforms );
			}	

			return $results;						
		}


		static function getDeviceTypes ( $data, $otherAsDefault = true )
		{
			$results = [];


			if ( is_array($data) )
				$values = $data;
			else
				$values = explode( ',' , $data );	


			foreach ( $values as $v )
			{
				$platforms = preg_split( '/[^a-zA-Z\d]/', $v, null, PREG_SPLIT_NO_EMPTY );

				foreach ( $platforms as $p )
				{
					switch ( strtolower($p) )
					{
						case 'android_phone':
						case 'iphone':
						case 'smartphone':
							if ( !in_array( 'Smartphone', $results) )
								$results[] = 'Smartphone';
						break;
						case 'android_tablet':
						case 'ipad':
						case 'tablet':						
							if ( !in_array( 'Tablet', $results) )
								$results[] = 'Tablet';						
						break;
						default:
							if ( $otherAsDefault && !in_array( 'Other', $results) )
								$results[] = 'Other';
						break;
					}
				}

				unset ( $platforms );
			}	

			return $results;						
		}


		static function getValues ( $data, $delimiter = ',' )
		{
			$results = [];

			if ( is_array($data) )
				$values = $data;
			else
				$values = explode( $delimiter , $data );	


			foreach ( $values as $v )
			{
				$platforms = preg_split( '/[^a-z.A-Z\d]/', $v, null, PREG_SPLIT_NO_EMPTY );

				foreach ( $platforms as $p )
				{
					$results[] = $p;
				}

				unset ( $platforms );
			}	

			return $results;						
		}


		static function getCarriers ( $data, $carriersDataProvider )
		{
			$result = [];

			if ( is_array($data) )
			{
				$values = $data;
			}
			else
			{
				$values = explode( ',' , $data );
			}

			foreach ( $carriersDataProvider as $carrier )
			{
				foreach ( $values as $v )
				{
					$names = preg_split( '/[^a-zA-Z\d]/', $v, null, PREG_SPLIT_NO_EMPTY );

					foreach ( $names as $n )
					{
						if ( strtolower($n) == strtolower($carrier->name) )
							$result[] = $carrier->name;
					}
				}	
			}

			return $result;						
		}				

	}

?>