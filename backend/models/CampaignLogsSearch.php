<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\CampaignLogs;

/**
 * CampaignLogsSearch represents the model behind the search form about `backend\models\CampaignLogs`.
 */
class CampaignLogsSearch extends CampaignLogs
{

    public $campaign;
    public $clusterLog;
    public $country;
    public $placement;
    public $publisher;
    public $model;
    public $status;
    public $placement_id; 
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['click_id', 'session_hash', 'click_time', 'conv_time', 'campaign', 'clusterLog', 'country', 'placement', 'publisher', 'status', 'model'], 'safe'],
            [['D_Campaign_id'], 'integer'],
            [['revenue'], 'number'],
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
        $query = CampaignLogs::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }


        // fields
        $query->select([
            '*',
            'D_Placement.model AS model',
            'D_Placement.Publishers_name as publisher',
            'D_Placement.status AS status',
        ]);


        // relations
        $query->joinWith([
            'campaign',
            'clusterLog',
            'clusterLog.placement',
        ]);



        // sorting
        $dataProvider->sort->attributes['campaign'] = [
            'asc' => ['D_Campaign.name' => SORT_ASC],
            'desc' => ['D_Campaign.name' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['affiliate'] = [
            'asc' => ['D_Campaign.Affiliates_name' => SORT_ASC],
            'desc' => ['D_Campaign.Affiliates_name' => SORT_DESC],
        ];  
        $dataProvider->sort->attributes['publisher'] = [
            'asc' => ['D_Placement.Publishers_name' => SORT_ASC],
            'desc' => ['D_Placement.Publishers_name' => SORT_DESC],
        ];   
        $dataProvider->sort->attributes['model'] = [
            'asc' => ['D_Placement.model' => SORT_ASC],
            'desc' => ['D_Placement.model' => SORT_DESC],
        ];               
        $dataProvider->sort->attributes['status'] = [
            'asc' => ['D_Placement.status' => SORT_ASC],
            'desc' => ['D_Placement.status' => SORT_DESC],
        ];            
        $dataProvider->sort->attributes['country'] = [
            'asc' => ['F_ClusterLogs.country' => SORT_ASC],
            'desc' => ['F_ClusterLogs.country' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['connection_type'] = [
            'asc' => ['F_ClusterLogs.connection_type' => SORT_ASC],
            'desc' => ['F_ClusterLogs.connection_type' => SORT_DESC],
        ];        
        $dataProvider->sort->attributes['carrier'] = [
            'asc' => ['F_ClusterLogs.carrier' => SORT_ASC],
            'desc' => ['F_ClusterLogs.carrier' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['device'] = [
            'asc' => ['F_ClusterLogs.device' => SORT_ASC],
            'desc' => ['F_ClusterLogs.device' => SORT_DESC],
        ];
      
        $dataProvider->sort->attributes['device_brand'] = [
            'asc' => ['F_ClusterLogs.device_brand' => SORT_ASC],
            'desc' => ['F_ClusterLogs.device_brand' => SORT_DESC],
        ];        
        $dataProvider->sort->attributes['device_model'] = [
            'asc' => ['F_ClusterLogs.device_model' => SORT_ASC],
            'desc' => ['F_ClusterLogs.device_model' => SORT_DESC],
        ];        
        $dataProvider->sort->attributes['os'] = [
            'asc' => ['F_ClusterLogs.os' => SORT_ASC],
            'desc' => ['F_ClusterLogs.os' => SORT_DESC],
        ];        
        $dataProvider->sort->attributes['os_version'] = [
            'asc' => ['F_ClusterLogs.os_version' => SORT_ASC],
            'desc' => ['F_ClusterLogs.os_version' => SORT_DESC],
        ];        
        $dataProvider->sort->attributes['browser'] = [
            'asc' => ['F_ClusterLogs.browser' => SORT_ASC],
            'desc' => ['F_ClusterLogs.browser' => SORT_DESC],
        ];        
        $dataProvider->sort->attributes['browser_version'] = [
            'asc' => ['F_ClusterLogs.browser_version' => SORT_ASC],
            'desc' => ['F_ClusterLogs.browser_version' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['cost'] = [
            'asc' => ['F_ClusterLogs.cost' => SORT_ASC],
            'desc' => ['F_ClusterLogs.cost' => SORT_DESC],
        ];        
        $dataProvider->sort->attributes['imps'] = [
            'asc' => ['F_ClusterLogs.imps' => SORT_ASC],
            'desc' => ['F_ClusterLogs.imps' => SORT_DESC],
        ];        




        // grid filtering conditions
        $query->andFilterWhere([
            //'D_Campaign_id' => $this->D_Campaign_id,
            //'click_time' => $this->click_time,
            //'conv_time' => $this->conv_time,
            //'revenue' => $this->revenue,
        ]);

        $query->andFilterWhere(['like', 'click_id', $this->click_id])
            ->andFilterWhere(['like', 'session_hash', $this->session_hash]);

        return $dataProvider;
    }
}
