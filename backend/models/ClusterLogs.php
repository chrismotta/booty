<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "F_ClusterLogs".
 *
 * @property string $session_hash
 * @property integer $D_Placement_id
 * @property integer $cluster_id
 * @property integer $imps
 * @property string $imp_time
 * @property string $country
 * @property string $connection_type
 * @property string $carrier
 * @property string $device
 * @property string $device_model
 * @property string $device_brand
 * @property string $os
 * @property string $os_version
 * @property string $browser
 * @property string $browser_version
 * @property string $cost
 */
class ClusterLogs extends \yii\db\ActiveRecord
{
    public $placement;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'F_ClusterLogs';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['session_hash', 'D_Placement_id', 'cluster_id', 'imps'], 'required'],
            [['D_Placement_id', 'cluster_id', 'imps'], 'integer'],
            [['imp_time'], 'safe'],
            [['connection_type', 'device'], 'string'],
            [['cost'], 'number'],
            [['session_hash', 'carrier', 'device_model', 'device_brand', 'os', 'os_version', 'browser', 'browser_version'], 'string', 'max' => 255],
            [['country'], 'string', 'max' => 2],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'session_hash' => 'Session Hash',
            'D_Placement_id' => 'Placement ID',
            'cluster_id' => 'Cluster ID',
            'imps' => 'Imps',
            'imp_time' => 'Imp Time',
            'country' => 'Country',
            'connection_type' => 'Connection Type',
            'carrier' => 'Carrier',
            'device' => 'Device',
            'device_model' => 'Device Model',
            'device_brand' => 'Device Brand',
            'os' => 'Os',
            'os_version' => 'Os Version',
            'browser' => 'Browser',
            'browser_version' => 'Browser Version',
            'cost' => 'Cost',
        ];
    }

    public function getPlacement()
    {
        return $this->hasOne(DPlacement::className(), ['id' => 'D_Placement_id']);
    }      
}
