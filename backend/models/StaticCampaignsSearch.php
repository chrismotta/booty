<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\StaticCampaigns;

/**
 * StaticCampaignsSearch represents the model behind the search form about `app\models\StaticCampaigns`.
 */
class StaticCampaignsSearch extends StaticCampaigns
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name', 'landing_url', 'creative_300x250', 'creative_320x50'], 'safe'],
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
        $query = StaticCampaigns::find();

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
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'landing_url', $this->landing_url])
            ->andFilterWhere(['like', 'creative_300x250', $this->creative_300x250])
            ->andFilterWhere(['like', 'creative_320x50', $this->creative_320x50]);

        $query->andWhere(['!=', 'StaticCampaigns.status', 'archived']);

        return $dataProvider;
    }
}
