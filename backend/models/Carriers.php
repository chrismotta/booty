<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "Carriers".
 *
 * @property string $country_alpha2_code
 * @property string $carrier_name
 */
class Carriers extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Carriers';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['country_alpha2_code'], 'string', 'max' => 2],
            [['carrier_name'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'country_alpha2_code' => 'Country Alpha2 Code',
            'carrier_name' => 'Carrier Name',
        ];
    }

    public static function getListByCountry($countryCode){
        $carriers = self::find();
        $carriers->select(['country_alpha2_code', 'country_name']);
        $carriers->orderBy(['country_name' => SORT_ASC]);
        $result = $carriers->asArray()->all();
        return ArrayHelper::map($result, 'country_alpha2_code', 'country_name');
    }
}
