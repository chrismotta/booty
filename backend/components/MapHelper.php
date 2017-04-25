<?php

	namespace backend\components;
	 
	 
	use Yii;
	use yii\base\Component;
	use yii\base\InvalidConfigException;
	 
	class MapHelper extends Component
	{
	
		static function arrayToMap ( array $data )
		{
			$r = [];

			foreach ( $data as $value )
			{
				$r[$value] = $value;
			}

			return $r;
		}


		static function redisToMap ( array $data )
		{
			$k = 0;
			$r = [];
			$kn = false;

			foreach ( $data as $value )
			{
				if ( $k%2 == 0 || $k==0 )
				{
					$kn = $value;
				}
				else
				{
					$r[$kn] = $value; 	
				}

				$k++;
			} 

			return $r;
		}

	}

?>