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
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'Affiliates_id'], 'integer'],
            [['name', 'landing_url', 'creative_320x50', 'creative_300x250'], 'safe'],
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
            'Affiliates_id' => $this->Affiliates_id,
            'payout' => $this->payout,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'landing_url', $this->landing_url])
            ->andFilterWhere(['like', 'creative_320x50', $this->creative_320x50])
            ->andFilterWhere(['like', 'creative_300x250', $this->creative_300x250]);

        return $dataProvider;
    }
}
