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
            [['Countries_country_alpha2_code'], 'string', 'max' => 2],
            [['carrier_name'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'Countries_country_alpha2_code' => 'Country Alpha2 Code',
            'carrier_name' => 'Carrier Name',
        ];
    }

    /**
    * @return \yii\db\ActiveQuery
    */
   public function getCampaigns()
   {
       return $this->hasMany(Campaigns::className(), ['Carriers_id' => 'id']);
   }

   /**
    * @return \yii\db\ActiveQuery
    */
   public function getClusters()
   {
       return $this->hasMany(Clusters::className(), ['Carriers_id' => 'id']);
   }

    public static function getListByCountry($countryCode=null){
        $carriers = self::find();
        $carriers->select(['id', 'carrier_name']);
        if(isset($countryCode))
            $carriers->filterWhere(['Countries_country_alpha2_code'=>$countryCode]);
        $carriers->orderBy(['carrier_name' => SORT_ASC]);
        $result = $carriers->asArray()->all();
        return ArrayHelper::map($result, 'id', 'carrier_name');
    }
}
