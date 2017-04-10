<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Report;

/**
 * ReportSearch represents the model behind the search form about `app\models\Report`.
 */
class ReportSearch extends Report
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'D_Placement_id', 'D_Campaign_id', 'cluster_id', 'imps'], 'integer'],
            [['session_hash', 'imp_time', 'click_id', 'click_time', 'conv_time', 'country', 'connection_type', 'carrier', 'device', 'device_model', 'device_brand', 'os', 'os_version', 'browser', 'browser_version'], 'safe'],
            [['cost'], 'number'],
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
        $query = Report::find();

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
            'id' => $this->id,
            'D_Placement_id' => $this->D_Placement_id,
            'D_Campaign_id' => $this->D_Campaign_id,
            'cluster_id' => $this->cluster_id,
            'imps' => $this->imps,
            'imp_time' => $this->imp_time,
            'cost' => $this->cost,
            'click_time' => $this->click_time,
            'conv_time' => $this->conv_time,
        ]);

        $query->andFilterWhere(['like', 'session_hash', $this->session_hash])
            ->andFilterWhere(['like', 'click_id', $this->click_id])
            ->andFilterWhere(['like', 'country', $this->country])
            ->andFilterWhere(['like', 'connection_type', $this->connection_type])
            ->andFilterWhere(['like', 'carrier', $this->carrier])
            ->andFilterWhere(['like', 'device', $this->device])
            ->andFilterWhere(['like', 'device_model', $this->device_model])
            ->andFilterWhere(['like', 'device_brand', $this->device_brand])
            ->andFilterWhere(['like', 'os', $this->os])
            ->andFilterWhere(['like', 'os_version', $this->os_version])
            ->andFilterWhere(['like', 'browser', $this->browser])
            ->andFilterWhere(['like', 'browser_version', $this->browser_version]);

        return $dataProvider;
    }
}
