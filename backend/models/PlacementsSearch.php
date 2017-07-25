<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Placements;

/**
 * PlacementsSearch represents the model behind the search form about `app\models\Placements`.
 */
class PlacementsSearch extends Placements
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'Publishers_id','Clusters_id', 'frequency_cap', 'health_check_imps'], 'integer'],
            [['name', 'model', 'status', 'size', 'publisher', 'cluster' ], 'safe'],
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
        $query = Placements::find();
        $query->joinWith(['publishers']);
        $query->joinWith(['clusters']);
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['attributes' => [ 'id','name', 'frequency_cap','payout', 'publisher', 'cluster']]
        ]);

        $query->select([
            'Placements.id',
            'frequency_cap',
            'Placements.name',
            'payout',
            'Publishers.name as publisher',
            'Clusters.name as cluster'
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
            'Publishers_id' => $this->Publishers_id,
            'Clusters_id' => $this->Clusters_id,
            'frequency_cap' => $this->frequency_cap,
            'payout' => $this->payout,
            'health_check_imps' => $this->health_check_imps,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'model', $this->model])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'size', $this->size]);

        return $dataProvider;
    }
}
