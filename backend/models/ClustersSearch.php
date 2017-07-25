<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Clusters;

/**
 * ClustersSearch represents the model behind the search form about `app\models\Clusters`.
 */
class ClustersSearch extends Clusters
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'StaticCampaigns_id'], 'integer'],
            [['name', 'country', 'connection_type', 'os', 'placement', 'static_campaign'], 'safe'],
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
        $query = Clusters::find();
        // $query->joinWith(['staticCampaigns']);
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
            'Clusters.id' => $this->id,
            'StaticCampaigns_id' => $this->StaticCampaigns_id,
        ]);

        $query->andFilterWhere(['like', 'Clusters.name', $this->name])
            ->andFilterWhere(['like', 'country', $this->country])
            ->andFilterWhere(['like', 'connection_type', $this->connection_type])
            ->andFilterWhere(['like', 'os', $this->os]);

        return $dataProvider;
    }
}
