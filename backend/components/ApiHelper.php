<?php

	namespace backend\components;
	 
	 
	use Yii;
	use yii\base\Component;
	use yii\base\InvalidConfigException;
	 
	class ApiHelper extends Component
	{
		static function getAppIdFromUrl ( $value )
		{
			$result = [];

			if ( filter_var($value, FILTER_VALIDATE_URL) )
			{
				$matches = array();

				if ( preg_match( '/(apple.com)/', $value ) )
				{
					$matches = [];
					preg_match( '/(id)[0-9]+/', $value, $matches );

					if ( !empty($matches) )
					{
						$result['ios'] = substr( $matches[0], 2 );
					}
				}

				if ( preg_match( '/(google.com)/', $value ) )
				{
					$matches = [];
					preg_match( '/(id=)[a-zA-Z0-9.]+/', $value, $matches );

					if ( !empty($matches) )
					{
						$result['android'] = substr( $matches[0], 3 );

					}
				}				
			}

			return $result;
		}


		static function cleanAppleId ( $value )
		{
			$matches = [];
			preg_match( '/(id)[0-9]+/', $value, $matches );

			if ( !empty($matches) )
			{
				return substr( $matches[0], 2 );
			}

			return $value;	
		}


		static function getOs ( $data, $otherAsDefault = true )
		{
			$results = [];

			if ( is_array($data) )
				$values = $data;
			else
				$values = preg_split( '/[^a-zA-Z\d]/', $data, null, PREG_SPLIT_NO_EMPTY );

			foreach ( $values as $value )
			{
				switch ( strtolower($value) )
				{
					case 'ios':
					case 'ipod':
					case 'ipad':
					case 'iphone':
						if ( !in_array( 'iOS', $results) )
							$results[] = 'iOS';
					break;
					case 'android':
					case 'android_tablet':
					case 'android (tablet)':
					case 'android(tablet)':
					case 'android tablet':		
					case 'android_phone':
					case 'android phone':				
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
						case 'android phone':
						case 'iphone':
						case 'smartphone':
							if ( !in_array( 'Smartphone', $results) )
								$results[] = 'Smartphone';
						break;
						case 'android_tablet':
						case 'android (tablet)':
						case 'android(tablet)':
						case 'android tablet':
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