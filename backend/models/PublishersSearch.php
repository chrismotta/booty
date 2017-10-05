<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Publishers;
use common\models\User;

/**
 * PublishersSearch represents the model behind the search form about `app\models\Publishers`.
 */
class PublishersSearch extends Publishers
{


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'admin_user'], 'integer'],
            [['name', 'short_name', 'admin_user'], 'safe'],
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
        $query = Publishers::find();
        $query->joinWith(['adminUser']);

        
        // add conditions that should always apply here

        $query->select([
            'Publishers.id',
            'name',
            'short_name',
            'user.username as username'
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['attributes' => [ 'id','name', 'short_name', 'username']]
        ]);


        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'Publishers.id' => $this->id,
            'Publishers.admin_user' => $this->admin_user,
        ]);


        // role filter
        $userroles = User::getRolesByID(Yii::$app->user->getId());
        if(in_array('Advisor', $userroles) || in_array('Stakeholder', $userroles)){
            $assignedPublishers = Publishers::getPublishersByUser(Yii::$app->user->getId());
            $query->andWhere( ['in', 'Publishers.id', $assignedPublishers] );
        } 

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'short_name', $this->short_name]);

        $query->andWhere(['!=', 'Publishers.status', 'archived']);

        return $dataProvider;
    }

    public static function searchForFilter($q=null)
    {
        $name_id = 'CONCAT( name, " (", id, ")" )';

        $query = Publishers::find();
        $query->select([$name_id . ' as name_id', 'id']);
        $query->orderBy( [ 'name_id' => SORT_ASC ] );

        // role filter
        $userroles = User::getRolesByID(Yii::$app->user->getId());
        if(in_array('Advisor', $userroles) || in_array('Stakeholder', $userroles)){
            $assignedPublishers = Publishers::getPublishersByUser(Yii::$app->user->getId());
            $query->andWhere( ['in', 'id', $assignedPublishers] );
        }

        if(isset($q))
            $query->andWhere( ['like', $name_id, $q] );
        
        $query->andWhere(['!=', 'status', 'archived']);
        
        return $query->asArray()->all();
    }
}
