<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "Countries".
 *
 * @property string $country_alpha2_code
 * @property string $country_alpha3_code
 * @property string $country_numeric_code
 * @property string $country_name
 */
class Countries extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Countries';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['country_alpha2_code'], 'string', 'max' => 2],
            [['country_alpha3_code', 'country_numeric_code'], 'string', 'max' => 3],
            [['country_name'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'country_alpha2_code' => 'Country Alpha2 Code',
            'country_alpha3_code' => 'Country Alpha3 Code',
            'country_numeric_code' => 'Country Numeric Code',
            'country_name' => 'Country Name',
        ];
    }

    public static function getList(){
        $countries = self::find();
        $countries->select(['country_alpha2_code', 'country_name']);
        $countries->orderBy(['country_name' => SORT_ASC]);
        $result = $countries->asArray()->all();
        return ArrayHelper::map($result, 'country_alpha2_code', 'country_name');
    }
}
