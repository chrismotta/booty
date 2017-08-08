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
        $query->joinWith(['affiliates']);
        
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['attributes' => [ 'id','name', 'landing_url','payout', 'affiliate', 'country', 'os', 'carrier', 'device_type', 'os_version', 'connection_type']]            
        ]);

        $query->select([
            'Campaigns.id',
            'landing_url',
            'Campaigns.name',
            'Campaigns.carrier',
            'payout',
            'Affiliates.name AS affiliate',
            'Campaigns.os',
            'Campaigns.os_version',
            'Campaigns.connection_type',
            'Campaigns.country',
            'Campaigns.device_type',
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
            'payout' => $this->payout,
        ]);

        $query->andFilterWhere(['like', 'Campaigns.name', $this->name])
            ->andFilterWhere(['like', 'landing_url', $this->landing_url])
            ->andFilterWhere(['like', 'creative_320x50', $this->creative_320x50])
            ->andFilterWhere(['like', 'creative_300x250', $this->creative_300x250])
            ->andFilterWhere(['like', 'country', $this->country])
            ->andFilterWhere(['like', 'carrier', $this->carrier])
            ->andFilterWhere(['like', 'os', $this->os])
            ->andFilterWhere(['like', 'os_version', $this->os_version])
            ->andFilterWhere(['like', 'device_type', $this->device_type])
            ->andFilterWhere(['like', 'connection_type', $this->connection_type]);

        $query->andWhere(['!=', 'Campaigns.status', 'archived']);

        return $dataProvider;
    }

    public function searchAvailable($params, $clusterID)
    {
        $query = Campaigns::find();
        $query->joinWith(['affiliates']);

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
            'Campaigns.id'  => $this->id,
            'Affiliates_id' => $this->Affiliates_id,
            'payout'        => $this->payout,
        ]);

        $query->andFilterWhere(['like', 'Campaigns.name', $this->name])
            ->andFilterWhere(['like', 'landing_url', $this->landing_url])
            ->andFilterWhere(['like', 'creative_320x50', $this->creative_320x50])
            ->andFilterWhere(['like', 'creative_300x250', $this->creative_300x250])
            ->andFilterWhere(['like', 'Affiliates.name', $this->affiliateName])
            // ->andFilterWhere(['like', 'country', $this->country])
            // ->andFilterWhere(['like', 'os', $this->os])
            // ->andFilterWhere(['>=', 'os_version', $this->os_version])
            // ->andFilterWhere(['like', 'device_type', $this->device_type])
            //->andFilterWhere(['like', 'connection_type', $this->connection_type])
            ;

        // NOTE: '=> null' doesn't work with andFilterWhere(), use filterWhere()

        if(isset($this->country))
            $query->andWhere([
                'or', 
                ['country' => null], 
                ['like', 'country', $this->country ]
                ]);
        
        if(isset($this->os))
            $query->andWhere([
                'or', 
                ['os' => null], 
                ['like', 'os', $this->os]
                ]);

        // if(isset($this->os_version))
        //     $query->andWhere([
        //         'or', 
        //         ['os_version' => null], 
        //         ['>=', 'os_version', $this->os_version]
        //         ]);
        
        if(isset($this->connection_type))
            $query->andWhere([
                'or', 
                ['connection_type' => null], 
                ['like', 'connection_type', $this->connection_type]
                ]);

        if(isset($this->device_type))
            $query->andWhere([
                'or', 
                ['device_type' => null], 
                ['like', 'device_type', $this->device_type]
                ]);

        if(isset($this->carrier))
            $query->andWhere([
                'or', 
                ['carrier' => null], 
                ['like', 'carrier', $this->carrier]
                ]);

        //$query->where(['country' => null])->orWhere(['=', 'country', $this->country]);
        //$query->andWhere(['os' => null])->orWhere(['like', 'os', $this->os]);
        $query->andWhere(['cc.Clusters_id' => null]);
        $query->andWhere(['!=', 'Campaigns.status', 'archived']);

        //var_export( $query->createCommand()->getRawSql() );die();

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
