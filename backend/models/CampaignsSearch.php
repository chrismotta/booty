<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Campaigns;

/**
 * CampaignsSearch represents the model behind the search form about `app\models\Campaigns`.
 */
class CampaignsSearch extends Campaigns
{
    public $affiliateName;
    public $carrierName;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'Affiliates_id'], 'integer'],
            [['name', 'landing_url', 'creative_320x50', 'creative_300x250', 'affiliateName', 'affiliate', 'country', 'os', 'connection_type', 'os_version', 'carrier', 'device_type'], 'safe'],
            [['payout'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Campaigns::find();
        $query->joinWith(['affiliates', 'carriers']);
        
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['attributes' => [ 'id','name', 'landing_url','payout', 'affiliate', 'country', 'os', 'carrier', 'device_type', 'os_version', 'connection_type']]            
        ]);

        $query->select([
            'Campaigns.id',
            'landing_url',
            'Campaigns.name',
            'payout',
            'Affiliates.name AS affiliate',
            'Campaigns.os',
            'Campaigns.os_version',
            'Campaigns.connection_type',
            'Campaigns.country',
            'Campaigns.device_type',
            'Carriers.carrier_name AS carrier'
        ]);

        $this->load($params);

        // The key is the attribute name on our "TourSearch" instance
        $dataProvider->sort->attributes['affiliateName'] = [
            // The tables are the ones our relation are configured to
            // in my case they are prefixed with "tbl_"
            'asc' => ['Affiliates.name' => SORT_ASC],
            'desc' => ['Affiliates.name' => SORT_DESC],
        ];

        $dataProvider->sort->attributes['carrierName'] = [
            // The tables are the ones our relation are configured to
            // in my case they are prefixed with "tbl_"
            'asc' => ['Carriers.carrier_name' => SORT_ASC],
            'desc' => ['Carriers.carrier_name' => SORT_DESC],
        ];        

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'Campaigns.id' => $this->id,
            'Affiliates_id' => $this->Affiliates_id,
            'Carriers_id' => $this->Carriers_id,
            'payout' => $this->payout,
        ]);

        $query->andFilterWhere(['like', 'Campaigns.name', $this->name])
            ->andFilterWhere(['like', 'landing_url', $this->landing_url])
            ->andFilterWhere(['like', 'creative_320x50', $this->creative_320x50])
            ->andFilterWhere(['like', 'creative_300x250', $this->creative_300x250])
            ->andFilterWhere(['like', 'country', $this->country])
            ->andFilterWhere(['like', 'os', $this->os])
            ->andFilterWhere(['like', 'os_version', $this->os_version])
            ->andFilterWhere(['like', 'device_type', $this->device_type])
            ->andFilterWhere(['like', 'connection_type', $this->connection_type]);


        return $dataProvider;
    }

    public function searchAvailable($params, $clusterID)
    {
        $query = Campaigns::find();
        $query->joinWith(['affiliates', 'carriers']);

        $subQuery = ClustersHasCampaigns::find()->where(['Clusters_id'=>$clusterID]);
        $query->leftJoin(['cc' => $subQuery], 'Campaigns.id = cc.Campaigns_id');
        
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        // The key is the attribute name on our "TourSearch" instance
        $dataProvider->sort->attributes['affiliateName'] = [
            // The tables are the ones our relation are configured to
            // in my case they are prefixed with "tbl_"
            'asc' => ['Affiliates.name' => SORT_ASC],
            'desc' => ['Affiliates.name' => SORT_DESC],
        ];

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'Campaigns.id' => $this->id,
            'Affiliates_id' => $this->Affiliates_id,
            'Carriers_id' => $this->Carriers_id,
            'payout' => $this->payout,
        ]);

        $query->andFilterWhere(['like', 'Campaigns.name', $this->name])
            ->andFilterWhere(['like', 'landing_url', $this->landing_url])
            ->andFilterWhere(['like', 'creative_320x50', $this->creative_320x50])
            ->andFilterWhere(['like', 'creative_300x250', $this->creative_300x250])
            ->andFilterWhere(['like', 'Affiliates.name', $this->affiliateName])
            ->andFilterWhere(['like', 'country', $this->country])
            ->andFilterWhere(['like', 'os', $this->os])
            ->andFilterWhere(['like', 'os_version', $this->os_version])
            ->andFilterWhere(['like', 'device_type', $this->device_type])
            ->andFilterWhere(['like', 'connection_type', $this->connection_type]);

        $query->andWhere(['cc.Clusters_id' => null]);

        return $dataProvider;
    }


    public function searchAssigned($clusterID){

        $query = Campaigns::find();
        $query->joinWith(['clusters']);

        $query->andFilterWhere(['Clusters.id'=>$clusterID]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->sort = false;

        return $dataProvider;
    }


    public function assignToCluster($clusterID){
        $cluster = Clusters::findOne($clusterID);
        $return = $this->link('clusters', $cluster);
        
        return  $return;
    }

    public function unassignToCluster($clusterID){
        $cluster = Clusters::findOne($clusterID);
        $return = $this->unlink('clusters', $cluster, true);

        return  $return;
    }

}
