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
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['click_id', 'session_hash', 'click_time', 'conv_time', 'campaign', 'clusterLog'], 'safe'],
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
