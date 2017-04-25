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

	}

?>