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
			{
				$values = [];

				foreach ( $data as $v )
				{
					$r = preg_split( '/[^a-zA-Z\d]/', $v, null, PREG_SPLIT_NO_EMPTY );
					$values = array_merge($values, $r);
				}
			}
			else
			{
				$values = preg_split( '/[^a-zA-Z\d]/', $data, null, PREG_SPLIT_NO_EMPTY );
			}

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
					case '':
					case false:
					case 0:
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
			{
				$values = [];

				foreach ( $data as $v )
				{
					$r = preg_split( '/[^a-zA-Z\d]/', $v, null, PREG_SPLIT_NO_EMPTY );
					$values = array_merge($values, $r);
				}
			}
			else
			{
				$values = preg_split( '/[^a-zA-Z\d]/', $data, null, PREG_SPLIT_NO_EMPTY );
			}

			foreach ( $values as $value )
			{
				switch ( strtolower($value) )
				{
					case 'android_phone':
					case 'android phone':
					case 'iphone':
					case 'phone':
					case 'mobile':
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
					case 'desktop':
						if ( !in_array( 'Desktop', $results) )
							$results[] = 'Desktop';					
					break;
					case 'all':
						if ( !in_array( 'Smartphone', $results) )
							$results[] = 'Smartphone';					
						if ( !in_array( 'Tablet', $results) )
							$results[] = 'Tablet';					
						if ( !in_array( 'Desktop', $results) )
							$results[] = 'Desktop';
						if ( !in_array( 'Other', $results) )
							$results[] = 'Other';
					break;					
					case null:
					case '':
					case false:
					case 0:
					break;
					default:
						if ( $otherAsDefault && !in_array( 'Other', $results) )
							$results[] = 'Other';
					break;
				}
			}

			return $results;						
		}		

		static function getValues ( $data, $regex = ', ;:' )
		{
			$results = [];

			if ( is_array($data) )
			{
				$values = [];

				foreach ( $data as $v )
				{
					$r = preg_split( '/['.$regex.'^a-zA-Z\d]/', $v, null, PREG_SPLIT_NO_EMPTY );
					$values = array_merge($values, $r);
				}
			}
			else
			{
				$values = preg_split( '/[^a-z'.$regex.'A-Z\d]/', $data, null, PREG_SPLIT_NO_EMPTY );
			}	

			foreach ( $values as $value )
			{
				$results[] = $value;
			}

			return $results;						
		}

		static function getCarriers ( $data, $carriersDataProvider )
		{
			$result = [];

			if ( is_array($data) )
			{
				$values = [];

				foreach ( $data as $v )
				{
					$r = preg_split( '/[, |;:]/', $v, null, PREG_SPLIT_NO_EMPTY );
					$values = array_merge($values, $r);
				}
			}
			else
			{
				$values = preg_split( '/[, |;:]/', $data, null, PREG_SPLIT_NO_EMPTY );
			}


			foreach ( $values as $value )
			{
				foreach ( $carriersDataProvider as $carrier )
				{
					if ( strtolower($value) == strtolower($carrier->name) )
						$result[] = $carrier->name;
				}				
			}

			return $result;						
		}				

	}

?>