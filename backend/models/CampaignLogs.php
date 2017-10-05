<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "F_CampaignLogs".
 *
 * @property string $click_id
 * @property integer $D_Campaign_id
 * @property string $session_hash
 * @property string $click_time
 * @property string $conv_time
 * @property string $revenue
 */
class CampaignLogs extends \yii\db\ActiveRecord
{
    public $placement;
    public $placement_id;
    public $publisher; 
    public $publisher_id; 
    public $carrier;
    public $cluster;
    public $cluster_id;
    public $device;
    public $device_brand;
    public $device_model;
    public $os;
    public $os_version;
    public $browser;
    public $browser_version;    
    public $campaign;
    public $campaign_id;
    public $clusterlog;
    public $affiliate;
    public $affiliate_id;
    public $model;
    public $status;
    public $country;
    public $cost;
    public $imps;
    public $connection_type;
    public $convs;
    public $clicks;
    public $pub_id;
    public $subpub_id;
    public $exchange_id;
    public $device_id;
    public $revenue_ecpm;
    public $cost_ecpm;
    public $profit_ecpm;
    public $profit;
    public $conv_rate;
    public $column;
    public $date;
    public $unique_imps;
    public $userroles;
    

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'F_CampaignLogs';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['click_id', 'D_Campaign_id', 'session_hash'], 'required'],
            [['D_Campaign_id'], 'integer'],
            [['click_time', 'conv_time'], 'safe'],
            [['revenue'], 'number'],
            [['click_id', 'session_hash'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'click_id'      => 'Click ID',
            'D_Campaign_id' => 'Campaign ID',
            'session_hash'  => 'Session Hash',
            'click_time'    => 'Click Time',
            'conv_time'     => 'Conv Time',
            'revenue'       => 'Revenue',
            'pub_id'        => 'Pub ID',
            'subpub_id'     => 'Subpub ID',
            'exchange_id'   => 'Exchange ID',
            'device_id'     => 'Device ID',
            'profit'        => 'Profit',
            'revenue_ecpm'  => 'Revenue eCPM',
            'cost_ecpm'     => 'Cost eCPM',
            'profit_ecpm'   => 'Profit eCPM',
            'date'          => 'Date',
            'unique_imps'   => 'Unique Imps'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClusterLog()
    {
        return $this->hasOne(ClusterLogs::className(), ['session_hash' => 'session_hash']);
    }

    public function getCampaign()
    {
        return $this->hasOne(DCampaign::className(), ['id' => 'D_Campaign_id']);
    }    

}
