<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "F_Imp".
 *
 * @property integer $id
 * @property integer $D_Placement_id
 * @property integer $D_Campaign_id
 * @property integer $cluster_id
 * @property string $session_hash
 * @property integer $imps
 * @property string $imp_time
 * @property string $cost
 * @property string $click_id
 * @property string $click_time
 * @property string $conv_time
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
 */
class Report extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'F_Imp';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['D_Placement_id', 'cluster_id', 'session_hash', 'imps'], 'required'],
            [['D_Placement_id', 'D_Campaign_id', 'cluster_id', 'imps'], 'integer'],
            [['imp_time', 'click_time', 'conv_time'], 'safe'],
            [['cost'], 'number'],
            [['connection_type', 'device'], 'string'],
            [['session_hash', 'click_id', 'carrier', 'device_model', 'device_brand', 'os', 'os_version', 'browser', 'browser_version'], 'string', 'max' => 255],
            [['country'], 'string', 'max' => 2],
            [['session_hash'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'D_Placement_id' => 'D  Placement ID',
            'D_Campaign_id' => 'D  Campaign ID',
            'cluster_id' => 'Cluster ID',
            'session_hash' => 'Session Hash',
            'imps' => 'Imps',
            'imp_time' => 'Imp Time',
            'cost' => 'Cost',
            'click_id' => 'Click ID',
            'click_time' => 'Click Time',
            'conv_time' => 'Conv Time',
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
        ];
    }
}
