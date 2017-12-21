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
    public $carrierName;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'StaticCampaigns_id'], 'integer'],
            [['name', 'country', 'connection_type', 'os', 'os_version', 'placement', 'static_campaign', 'carrier', 'device_type'], 'safe'],
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
        $query->joinWith(['carriers']);
        // $query->joinWith(['staticCampaigns']);
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $query->select([
            'Clusters.id',
            'Clusters.name',
            'Clusters.os',
            'Clusters.os_version',
            'Clusters.connection_type',
            'Clusters.country',
            'Clusters.device_type',
            'Carriers.carrier_name AS carrier',
            'Clusters.min_payout',
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
            'Carriers_id' => $this->Carriers_id,
        ]);

        $dataProvider->sort->attributes['carrier'] = [
            // The tables are the ones our relation are configured to
            // in my case they are prefixed with "tbl_"
            'asc' => ['Carriers.carrier_name' => SORT_ASC],
            'desc' => ['Carriers.carrier_name' => SORT_DESC],
        ];   

        $query->andFilterWhere(['like', 'Clusters.name', $this->name])
            ->andFilterWhere(['like', 'country', $this->country])
            ->andFilterWhere(['like', 'connection_type', $this->connection_type])
            ->andFilterWhere(['like', 'device_type', $this->device_type])
            ->andFilterWhere(['like', 'os_version', $this->os_version])
            ->andFilterWhere(['like', 'os', $this->os]);

        $query->andWhere(['!=', 'Clusters.status', 'archived']);
        
        return $dataProvider;
    }

    public static function searchForFilter($q=null)
    {
        $name_id = 'CONCAT( name, " (", id, ")" )';

        $query = Clusters::find();
        $query->select([$name_id . ' as name_id', 'id']);
        $query->orderBy( [ 'name_id' => SORT_ASC ] );

        // role filter
        // $userroles = User::getRolesByID(Yii::$app->user->getId());
        // if(in_array('Advisor', $userroles)){
        //     $assignedPublishers = Publishers::getPublishersByUser(Yii::$app->user->getId());
        //     $query->andWhere( ['in', 'id', $assignedPublishers] );
        // }

        if(isset($q))
            $query->andWhere( ['like', $name_id, $q] );
        
        $query->andWhere(['!=', 'status', 'archived']);
        
        return $query->asArray()->all();
    }
}
