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
 * @property string $carrier
 * @property integer $StaticCampaigns_id
 *
 * @property Placements $placements
 * @property StaticCampaigns $staticCampaigns
 * @property ClustersHasCampaigns[] $clustersHasCampaigns
 * @property Campaigns[] $campaigns
 */
class Clusters extends \yii\db\ActiveRecord
{
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
            [['name', 'Placements_id', 'StaticCampaigns_id'], 'required'],
            [['Placements_id', 'StaticCampaigns_id'], 'integer'],
            [['connection_type'], 'string'],
            [['name', 'carrier'], 'string', 'max' => 255],
            [['country'], 'string', 'max' => 2],
            [['Placements_id'], 'exist', 'skipOnError' => true, 'targetClass' => Placements::className(), 'targetAttribute' => ['Placements_id' => 'id']],
            [['StaticCampaigns_id'], 'exist', 'skipOnError' => true, 'targetClass' => StaticCampaigns::className(), 'targetAttribute' => ['StaticCampaigns_id' => 'id']],
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
            'Placements_id' => 'Placements ID',
            'country' => 'Country',
            'connection_type' => 'Connection Type',
            'carrier' => 'Carrier',
            'StaticCampaigns_id' => 'Static Campaigns ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlacements()
    {
        return $this->hasOne(Placements::className(), ['id' => 'Placements_id']);
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
