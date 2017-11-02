<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Campaigns_Changelog".
 *
 * @property integer $id
 * @property integer $Campaigns_id
 * @property integer $Clusters_id
 * @property string $status
 * @property string $time
 *
 * @property Campaigns $campaigns
 * @property Clusters $clusters
 */
class CampaignsChangelog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Campaigns_Changelog';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Campaigns_id', 'status'], 'required'],
            [['id', 'Campaigns_id', 'Clusters_id'], 'integer'],
            [['status', 'desc'], 'string'],
            [['time'], 'safe'],
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
            'id' => 'ID',
            'Campaigns_id' => 'Campaigns ID',
            'Clusters_id' => 'Clusters ID',
            'desc' => 'Description',
            'status' => 'Status',
            'time' => 'Time',
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

    public static function log( $campaign_id, $status, $message = null, $cluster_id = null ) 
    {
        $log = new self();

        $log->Campaigns_id = $campaign_id;
        $log->Clusters_id  = $cluster_id;
        $log->status       = $status;
        $log->desc         = $message;

        return $log->save();
    }
}
