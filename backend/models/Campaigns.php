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
    public $click_macro;
    public $delivery_freq;
    public $clusters_id;
    public $affiliate;
    public $available;
    public $cluster;
    
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
            [['Affiliates_id', 'ext_id'], 'required'],
            [['id', 'Affiliates_id'], 'integer'],
            [['payout'], 'number'],
            [['status', 'info'], 'string'],
            [['country' ], 'string'],
            [['name', 'landing_url', 'creative_320x50', 'creative_300x250', 'os_version', 'carrier', 'os', 'connection_type', 'device_type', 'app_id'], 'string', 'max' => 255],
            [['os', 'connection_type', 'carrier', 'country', 'device_type', 'os_version'], 'default', 'value' => NULL],
            [['Affiliates_id'], 'exist', 'skipOnError' => true, 'targetClass' => Affiliates::className(), 'targetAttribute' => ['Affiliates_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'               => 'ID',
            'Affiliates_id'    => 'Affiliates ID',
            'name'             => 'Name',
            'payout'           => 'Payout',
            'landing_url'      => 'Landing Url',
            'creative_320x50'  => 'Creative 320x50',
            'creative_300x250' => 'Creative 300x250',
            'affiliateName'    => 'Affiliate',
            'connection_type'  => 'Conn. Type',
            'os'               => 'OS',
            'os_version'       => 'OS Version',
            'country'          => 'Country',
            'carrier'          => 'Carrier',
            'device_type'      => 'Dev. Type',
            'app_id'           => 'App ID',
            'delivery_freq'    => 'Freq.', 
            'ext_id'           => 'Ext. ID',
            'status'           => 'Status'
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

    public function formatValues($property, $style){
        $list = json_decode($this[$property]);

        $return = '';
            
        if(isset($list)){
            foreach ($list as $value) {
                $return.= '<span class="label label-'.$style.'">'.$value.'</span> ';
            }
        }

        return $return;
    }

    public static function getByCluster( $cluster_id ){
        $campaigns = self::find();
        $campaigns->joinWith(['clustersHasCampaigns', 'affiliates']);
        $campaigns->select(['Campaigns.id AS id', 'Campaigns.status AS status', 'Campaigns.landing_url AS landing_url', 'Campaigns.payout AS payout', 'Affiliates.click_macro AS click_macro']);
        $campaigns->where(['=', 'Clusters_has_Campaigns.Clusters_id', $cluster_id] );

       return $campaigns->all();
    }


    /**
     * @return int
     */
    // public static function getDeliveryFreq($Campaigns_id, $Clusters_id){
    //     $chc = ClustersHasCampaigns::findOne(['Campaigns_id' => $Campaigns_id, 'Clusters_id' => $Clusters_id]);
    //     return $chc->delivery_freq;
    // }
    public function getDeliveryFreq(){
        $cc = ClustersHasCampaigns::findOne(['Campaigns_id' => $this->id, 'Clusters_id' => $this->clusters_id]);
        return isset($cc) ? $cc->delivery_freq : '-';
    }

    public function unassignToCluster($clusterID){
        
        try {
        
            $cluster = Clusters::findOne($clusterID);
            $this->unlink('clusters', $cluster, true);
            return true;
            
        } catch (Exception $e) {
            
            return false;
        }
    }

    public function getLastLog(){

        $log = CampaignsChangelog::find()
        ->where([
            'Campaigns_id' => $this->id, 
            'Clusters_id' => $this->clusters_id
        ])
        ->orderBy('time DESC')
        ->one();
        return $log;
    }
    
}
