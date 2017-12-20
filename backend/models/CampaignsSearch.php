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
    public $affiliateName;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'Affiliates_id'], 'integer'],
            [['name', 'landing_url', 'creative_320x50', 'creative_300x250', 'affiliateName', 'ext_id', 'app_id', 'affiliate', 'country', 'os', 'connection_type', 'os_version', 'carrier', 'device_type', 'status'], 'safe'],
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

    public function searchByTarget($params)
    {

        $query = Campaigns::find();
        $query->joinWith(['affiliates']);
        $this->load($params);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['attributes' => [ 'country', 'os', 'affiliate', 'available' ]]            
        ]);

        $query->select([
            'Campaigns.country AS country',
            'Campaigns.os AS os',
            'Affiliates.name AS affiliate',
            'COUNT(Campaigns.id) AS available',
        ]);

        // grid filtering conditions
        $query->andFilterWhere(['like', 'Campaigns.country', $this->country])
            ->andFilterWhere(['like', 'Campaigns.os', $this->os])
            ->andFilterWhere(['like', 'Affiliates.name', $this->affiliate]);
        
        // fixed conditions
        $query->andWhere([
            'Affiliates.status' => 'active',
            'Campaigns.status' => 'active',
            'LENGTH(Campaigns.country)' => 6,
        ]);

        $query->groupBy([
            'Campaigns.country',
            'Campaigns.os',
            'Affiliates.name'
        ]);
        
        return $dataProvider;
    }

    public function searchByCluster($params)
    {

        $query = Clusters::find();
        $query->joinWith(['campaigns', 'campaigns.affiliates']);
        $this->load($params);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['attributes' => [ 'name', 'affiliate', 'assigned' ]]            
        ]);

        $query->select([
            'Clusters.name AS name',
            'Affiliates.name AS affiliate',
            'COUNT(Campaigns.id) AS assigned',
        ]);
        
        // grid filtering conditions
        $query->andFilterWhere(['like', 'Clusters.name', $this->name])
            ->andFilterWhere(['like', 'Affiliates.name', $this->affiliate]);
        
        // fixed conditions
        $query->andWhere([
            'Clusters.status' => 'active',
            'Affiliates.status' => 'active',
            'Campaigns.status' => 'active',
        ])
            ->andWhere(['>', 'Clusters_has_Campaigns.delivery_freq', 0]);

        $query->groupBy([
            'Clusters.name',
            'Affiliates.name'
        ]);
        
        return $dataProvider;
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
        $query->joinWith(['affiliates']);
        
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['attributes' => [ 'id','name', 'landing_url','payout', 'ext_id', 'affiliate', 'country', 'os', 'carrier', 'device_type', 'os_version', 'connection_type', 'status']]            
        ]);

        $query->select([
            'Campaigns.id',
            'landing_url',
            'Campaigns.name',
            'Campaigns.carrier',
            'payout',
            'Campaigns.ext_id AS ext_id',
            'Affiliates.name AS affiliate',
            'Campaigns.os',
            'Campaigns.os_version',
            'Campaigns.connection_type',
            'Campaigns.country',
            'Campaigns.device_type',
            'Campaigns.status',
        ]);

        $this->load($params);

        // The key is the attribute name on our "TourSearch" instance
        $dataProvider->sort->attributes['affiliateName'] = [
            // The tables are the ones our relation are configured to
            // in my case they are prefixed with "tbl_"
            'asc' => ['Affiliates.name' => SORT_ASC],
            'desc' => ['Affiliates.name' => SORT_DESC],
        ];      

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'Campaigns.id' => $this->id,
            'Affiliates_id' => $this->Affiliates_id,
            'payout' => $this->payout,
        ]);

        $query->andFilterWhere(['like', 'Campaigns.name', $this->name])
            ->andFilterWhere(['like', 'landing_url', $this->landing_url])
            ->andFilterWhere(['like', 'ext_id', $this->ext_id])
            ->andFilterWhere(['like', 'creative_320x50', $this->creative_320x50])
            ->andFilterWhere(['like', 'creative_300x250', $this->creative_300x250])
            ->andFilterWhere(['like', 'country', $this->country])
            ->andFilterWhere(['like', 'carrier', $this->carrier])
            ->andFilterWhere(['like', 'os', $this->os])
            ->andFilterWhere(['like', 'os_version', $this->os_version])
            ->andFilterWhere(['like', 'device_type', $this->device_type])
            ->andFilterWhere(['like', 'connection_type', $this->connection_type])
            ->andFilterWhere(['=', 'Campaigns.status', $this->status]);

        // $query->andWhere(['=', 'Campaigns.status', 'active']);

        return $dataProvider;
    }

    public function searchAvailable($params, $clusterID)
    {
        $query = Campaigns::find();
        $query->joinWith(['affiliates']);

        $subQuery = ClustersHasCampaigns::find()->where(['Clusters_id'=>$clusterID]);
        $query->leftJoin(['cc' => $subQuery], 'Campaigns.id = cc.Campaigns_id');
        
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination'=> [
                'defaultPageSize' => 500,
                'pageSizeLimit' => [1,500],
            ],
        ]);

        $this->load($params);

        // The key is the attribute name on our instance
        $dataProvider->sort->attributes['affiliateName'] = [
            // The tables are the ones our relation are configured to
            // in my case they are prefixed with "tbl_"
            'asc' => ['Affiliates.name' => SORT_ASC],
            'desc' => ['Affiliates.name' => SORT_DESC],
        ];

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'Campaigns.id'  => $this->id,
            'Affiliates_id' => $this->Affiliates_id,
            'payout'        => $this->payout,
        ]);

        $query->andFilterWhere(['like', 'Campaigns.name', $this->name])
            ->andFilterWhere(['like', 'landing_url', $this->landing_url])
            ->andFilterWhere(['like', 'creative_320x50', $this->creative_320x50])
            ->andFilterWhere(['like', 'creative_300x250', $this->creative_300x250])
            ->andFilterWhere(['like', 'Affiliates.name', $this->affiliateName])
            ->andFilterWhere(['like', 'Campaigns.ext_id', $this->ext_id])
            ->andFilterWhere(['like', 'Campaigns.app_id', $this->app_id])
            // ->andFilterWhere(['like', 'country', $this->country])
            // ->andFilterWhere(['like', 'os', $this->os])
            // ->andFilterWhere(['>=', 'os_version', $this->os_version])
            // ->andFilterWhere(['like', 'device_type', $this->device_type])
            //->andFilterWhere(['like', 'connection_type', $this->connection_type])
            ;

        // NOTE: '=> null' doesn't work with andFilterWhere(), use filterWhere()
        
        $query->andWhere(['Campaigns.status'=>'active']);

        if(isset($this->country))
            $query->andWhere([
                'or', 
                ['country' => null], 
                ['like', 'country', $this->country ]
                ]);
        
        if(isset($this->os))
            $query->andWhere([
                'or', 
                ['os' => null], 
                ['like', 'os', $this->os]
                ]);
        
        if(isset($this->connection_type))
            $query->andWhere([
                'or', 
                ['connection_type' => null], 
                ['like', 'connection_type', $this->connection_type]
                ]);

        if(isset($this->device_type))
            $query->andWhere([
                'or', 
                ['device_type' => null], 
                ['like', 'device_type', $this->device_type]
                ]);

        if(isset($this->carrier))
            $query->andWhere([
                'or', 
                ['carrier' => null], 
                ['like', 'carrier', $this->carrier]
                ]);

        //$query->where(['country' => null])->orWhere(['=', 'country', $this->country]);
        //$query->andWhere(['os' => null])->orWhere(['like', 'os', $this->os]);
        $query->andWhere(['cc.Clusters_id' => null]);
        $query->andWhere(['!=', 'Campaigns.status', 'archived']);

        //var_export( $query->createCommand()->getRawSql() );die();

        return $dataProvider;
    }


    public function searchAssigned($params, $clusterID=null){

        $query = Campaigns::find();
        $query->joinWith(['clusters', 'affiliates']);
        $query->select([
            'Campaigns.*', 
            'Clusters.id as clusters_id', 
            'Clusters_has_Campaigns.delivery_freq as delivery_freq',
        ]);

        $query->andFilterWhere(['Clusters.id'=>$clusterID]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination'=> [
                'defaultPageSize' => 500,
                'pageSizeLimit' => [1,500],
            ],
            'sort' => ['defaultOrder' => ['delivery_freq'=>SORT_DESC]],
        ]);

        $this->load($params);

        $dataProvider->sort->attributes['affiliateName'] = [
            'asc' => ['Affiliates.name' => SORT_ASC],
            'desc' => ['Affiliates.name' => SORT_DESC],
        ];

        $dataProvider->sort->attributes['delivery_freq'] = [
            'asc' => ['Clusters_has_Campaigns.delivery_freq' => SORT_ASC],
            'desc' => ['Clusters_has_Campaigns.delivery_freq' => SORT_DESC],
        ];

        // grid filtering conditions
        $query->andFilterWhere([
            'Campaigns.id'  => $this->id,
            'Affiliates_id' => $this->Affiliates_id,
            'payout'        => $this->payout,
        ]);

        $query->andFilterWhere(['like', 'Campaigns.name', $this->name])
            ->andFilterWhere(['like', 'landing_url', $this->landing_url])
            ->andFilterWhere(['like', 'creative_320x50', $this->creative_320x50])
            ->andFilterWhere(['like', 'creative_300x250', $this->creative_300x250])
            ->andFilterWhere(['like', 'Affiliates.name', $this->affiliateName])
            ->andFilterWhere(['like', 'Campaigns.ext_id', $this->ext_id])
            ->andFilterWhere(['like', 'Campaigns.app_id', $this->app_id])
            // ->andFilterWhere(['like', 'country', $this->country])
            // ->andFilterWhere(['like', 'os', $this->os])
            // ->andFilterWhere(['>=', 'os_version', $this->os_version])
            // ->andFilterWhere(['like', 'device_type', $this->device_type])
            //->andFilterWhere(['like', 'connection_type', $this->connection_type])
            ;

        if(isset($this->country))
            $query->andWhere([
                'or', 
                ['Campaigns.country' => null], 
                ['like', 'Campaigns.country', $this->country ]
                ]);
        
        if(isset($this->os))
            $query->andWhere([
                'or', 
                ['Campaigns.os' => null], 
                ['like', 'Campaigns.os', $this->os]
                ]);
        
        if(isset($this->connection_type))
            $query->andWhere([
                'or', 
                ['Campaigns.connection_type' => null], 
                ['like', 'Campaigns.connection_type', $this->connection_type]
                ]);

        if(isset($this->device_type))
            $query->andWhere([
                'or', 
                ['Campaigns.device_type' => null], 
                ['like', 'Campaigns.device_type', $this->device_type]
                ]);

        if(isset($this->carrier))
            $query->andWhere([
                'or', 
                ['Campaigns.carrier' => null], 
                ['like', 'Campaigns.carrier', $this->carrier]
                ]);

        return $dataProvider;
    }


    public function assignToCluster($clusterID){

        try {

            $cluster = Clusters::findOne($clusterID);
            $this->link('clusters', $cluster);
            return true;
            
        } catch (Exception $e) {
            
            return false;
        }

        
    }

    public function unassignToCluster($clusterID){
        
        try {
        
            $cluster = Clusters::findOne($clusterID);
            $this->unlink('clusters', $cluster, true);
            return true;
            
        } catch (Exception $e) {
            
            return false;
        }
    }

    public static function searchForFilter($q=null)
    {
        $name_id = 'CONCAT( name, " (", id, ")" )';

        $query = Campaigns::find();
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
