<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Clusters".
 *
 * @property integer $id
 * @property string $name
 * @property integer $Placements_id
 * @property string $country
 * @property string $connection_type
 * @property string $os
 * @property integer $StaticCampaigns_id
 *
 * @property Placements $placements
 * @property StaticCampaigns $staticCampaigns
 * @property ClustersHasCampaigns[] $clustersHasCampaigns
 * @property Campaigns[] $campaigns
 */
class Clusters extends \yii\db\ActiveRecord
{
    public $static_campaign;
    public $carrier;
    public $affiliate;
    public $available;
    // public $cluster;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Clusters';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'StaticCampaigns_id'], 'required'],
            [['StaticCampaigns_id', 'Carriers_id'], 'integer'],
            [['os_version', 'min_payout'], 'number'],
            [['connection_type', 'os', 'os_version', 'device_type'], 'string', 'skipOnEmpty'=>true ],
            [['os', 'connection_type', 'country', 'device_type'], 'default', 'value' => NULL],            
            [['name'], 'string', 'max' => 255],
            [['country'], 'string', 'max' => 2, 'skipOnEmpty'=>true ],
            [['StaticCampaigns_id'], 'exist', 'skipOnError' => true, 'targetClass' => StaticCampaigns::className(), 'targetAttribute' => ['StaticCampaigns_id' => 'id']],
            [['Carriers_id'], 'exist', 'skipOnError' => true, 'targetClass' => Carriers::className(), 'targetAttribute' => ['Carriers_id' => 'id']],            
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
            'country' => 'Country',
            'connection_type' => 'Connection Type',
            'os' => 'OS',
            'StaticCampaigns_id' => 'Static Campaigns ID',
            'Carriers_id' => 'Carrier',
            'device_type' => 'Device Type',
            'carrier'   => 'Carrier',
            'os_version'    => 'OS Version'
        ];
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStaticCampaigns()
    {
        return $this->hasOne(StaticCampaigns::className(), ['id' => 'StaticCampaigns_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCarriers()
    {
        return $this->hasOne(Carriers::className(), ['id' => 'Carriers_id']);
    }    

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClustersHasCampaigns()
    {
        return $this->hasMany(ClustersHasCampaigns::className(), ['Clusters_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCampaigns()
    {
        return $this->hasMany(Campaigns::className(), ['id' => 'Campaigns_id'])->viaTable('Clusters_has_Campaigns', ['Clusters_id' => 'id']);
    }


    /**
     * Get all avaliable and assigned roles/permission
     * @return array
     */
    public function getItems()
    {
        $avaliable = [];
        $assigned = [];
        
        /*
        $manager = Yii::$app->getAuthManager();
        foreach (array_keys($manager->getRoles()) as $name) {
            $avaliable[$name] = 'role';
        }

        foreach (array_keys($manager->getPermissions()) as $name) {
            if ($name[0] != '/') {
                $avaliable[$name] = 'permission';
            }
        }

        foreach ($manager->getAssignments($this->id) as $item) {
            $assigned[$item->roleName] = $avaliable[$item->roleName];
            unset($avaliable[$item->roleName]);
        }
         */
        
        $avaliable['bla'] = ['ble'];
        $avaliable['fra'] = ['fre'];

        return[
            'avaliable' => $avaliable,
            'assigned' => $assigned
        ];
    }
}
