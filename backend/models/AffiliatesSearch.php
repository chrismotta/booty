<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Affiliates;

/**
 * AffiliatesSearch represents the model behind the search form about `app\models\Affiliates`.
 */
class AffiliatesSearch extends Affiliates
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'admin_user'], 'integer'],
            [['name', 'short_name', 'user_id', 'api_key', 'admin_user'], 'safe'],
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
        $query = Affiliates::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $query->joinWith(['adminUser']);

        $query->select([
            'Affiliates.id',
            'name',
            'short_name',
            'user_id',
            'user.username as username'
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['attributes' => [ 'id','name', 'short_name','user_id', 'api_key', 'username']]
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
            'admin_user' => $this->admin_user,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'short_name', $this->short_name])
            ->andFilterWhere(['like', 'user_id', $this->user_id])
            ->andFilterWhere(['like', 'api_key', $this->api_key]);

        return $dataProvider;
    }
}
