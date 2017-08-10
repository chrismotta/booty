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
				if ( $value && $value!='NULL' )
					$r[$value] = $value;
			}

			return $r;
		}

		static function filtersFromRedisToSelectWidget ( array $data )
		{
			$r = [];

			natcasesort($data);
			
			foreach ( $data as $value )
			{
				$item = json_decode($value);

				if ( $item ){
					$id 	= $item->id;
					$r[$id] = $item->name; 
				}
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