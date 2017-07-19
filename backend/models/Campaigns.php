<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Campaigns".
 *
 * @property integer $id
 * @property integer $Affiliates_id
 * @property string $name
 * @property string $payout
 * @property string $landing_url
 * @property string $creative_320x50
 * @property string $creative_300x250
 *
 * @property Affiliates $affiliates
 * @property ClustersHasCampaigns[] $clustersHasCampaigns
 * @property Clusters[] $clusters
 */
class Campaigns extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Campaigns';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'Affiliates_id', 'name', 'payout', 'landing_url'], 'required'],
            [['id', 'Affiliates_id'], 'integer'],
            [['payout'], 'number'],
            [['name', 'landing_url', 'creative_320x50', 'creative_300x250'], 'string', 'max' => 255],
            [['Affiliates_id'], 'exist', 'skipOnError' => true, 'targetClass' => Affiliates::className(), 'targetAttribute' => ['Affiliates_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'Affiliates_id' => 'Affiliates ID',
            'name' => 'Name',
            'payout' => 'Payout',
            'landing_url' => 'Landing Url',
            'creative_320x50' => 'Creative 320x50',
            'creative_300x250' => 'Creative 300x250',
            'affiliateName' => 'Affiliate',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAffiliates()
    {
        return $this->hasOne(Affiliates::className(), ['id' => 'Affiliates_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClustersHasCampaigns()
    {
        return $this->hasMany(ClustersHasCampaigns::className(), ['Campaigns_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClusters()
    {
        return $this->hasMany(Clusters::className(), ['id' => 'Clusters_id'])->viaTable('Clusters_has_Campaigns', ['Campaigns_id' => 'id']);
    }
}
