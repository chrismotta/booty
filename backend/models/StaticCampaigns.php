<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "StaticCampaigns".
 *
 * @property integer $id
 * @property string $name
 * @property string $landing_url
 * @property string $creative_300x250
 * @property string $creative_320x50
 *
 * @property Clusters[] $clusters
 */
class StaticCampaigns extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'StaticCampaigns';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'landing_url', 'creative_300x250', 'creative_320x50'], 'required'],
            [['name', 'landing_url', 'creative_300x250', 'creative_320x50'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'landing_url' => 'Landing Url',
            'creative_300x250' => 'Creative 300x250',
            'creative_320x50' => 'Creative 320x50',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClusters()
    {
        return $this->hasMany(Clusters::className(), ['StaticCampaigns_id' => 'id']);
    }
}
