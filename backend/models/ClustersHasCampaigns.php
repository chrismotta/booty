<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Clusters_has_Campaigns".
 *
 * @property integer $Clusters_id
 * @property integer $Campaigns_id
 * @property integer $delivery_freq 
 *
 * @property Campaigns $campaigns
 * @property Clusters $clusters
 */
class ClustersHasCampaigns extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Clusters_has_Campaigns';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Clusters_id', 'Campaigns_id'], 'required'],
            [['Clusters_id', 'Campaigns_id'], 'integer'],
            [['Clusters_id', 'Campaigns_id', 'delivery_freq', 'prev_freq'], 'integer'],
            [['autostopped'], 'boolean'],
            [['Campaigns_id'], 'exist', 'skipOnError' => true, 'targetClass' => Campaigns::className(), 'targetAttribute' => ['Campaigns_id' => 'id']],
            [['Clusters_id'], 'exist', 'skipOnError' => true, 'targetClass' => Clusters::className(), 'targetAttribute' => ['Clusters_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'Clusters_id' => 'Clusters ID',
            'Campaigns_id' => 'Campaigns ID',
            'delivery_freq' => 'Delivery Frequency', 
            'prev_freq'     => 'Previous Frequency',
            'autostopped'   => 'Autostopped'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCampaigns()
    {
        return $this->hasOne(Campaigns::className(), ['id' => 'Campaigns_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClusters()
    {
        return $this->hasOne(Clusters::className(), ['id' => 'Clusters_id']);
    }

}
