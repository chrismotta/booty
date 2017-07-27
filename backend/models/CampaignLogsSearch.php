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
    public $placement_id;
    public $date_start;
    public $date_end;
    public $show_columns;
    public $date_range;
    public $fields_group1;
    public $fields_group2;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['click_id', 'session_hash', 'click_time', 'conv_time', 'campaign', 'clusterLog', 'country', 'placement', 'publisher', 'status', 'model' ], 'safe'],
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

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        //$this->load($params);

        // validation
        /*
        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }
        */

        // fields
        $fields = [];
        $group  = [];

        if ( isset($params['CampaignLogsSearch']['fields_group1']) && !empty( $params['CampaignLogsSearch']['fields_group1'] ) )
        {
            $filterFields = $params['CampaignLogsSearch']['fields_group1'];

            if ( isset($params['CampaignLogsSearch']['fields_group2']) && !empty( $params['CampaignLogsSearch']['fields_group2'] ) )            
                $filterFields = array_merge( $filterFields, $params['CampaignLogsSearch']['fields_group2'] );

            foreach ( $filterFields as $field )
            {
                switch ( $field )
                {
                    case 'campaign':
                        $fields[] = 'D_Campaign.name AS campaign';
                        $group[]  = 'D_Campaign.name';
                    break;
                    case 'affiliate':
                        $fields[] = 'D_Campaign.Affiliates_name AS affiliate';
                        $group[]  = 'D_Campaign.Affiliates_name';
                    break;
                    case 'publisher':
                        $fields[] = 'D_Placement.Publishers_name AS publisher';
                        $group[]  = 'D_Placement.Publishers_name'; 
                    break;
                    case 'model':
                        $fields[] = 'D_Placement.model AS model';
                        $group[]  = 'D_Placement.model';
                    break;
                    case 'status':
                        $fields[] = 'D_Placement.status AS status';
                        $group[]  = 'D_Placement.status';
                    break;
                    case 'imps':
                    case 'cost':
                        $fields[] = 'sum(F_ClusterLogs.'.$field.') AS '.$field;
                    break;
                    case 'revenue':
                        $fields[] = 'sum(F_CampaignLogs.'.$field.') AS '.$field;
                    break;
                    case 'clicks':
                        $fields[] = 'count(F_CampaignLogs.click_time) AS '.$field;
                    break;         
                    case 'convs':
                        $fields[] = 'count(F_CampaignLogs.conv_time) AS '.$field;
                    break;                                                   
                    case 'cluster':
                        $fields[] = 'F_ClusterLogs.cluster_name AS '.$field;
                    break;    
                    case 'placement':
                        $fields[] = 'D_Placement.name AS '.$field;
                    break;                                         
                    default:
                        $fields[] = 'F_ClusterLogs.'.$field.' AS '.$field;
                    break;
                }
            }

            $query->groupBy( $group );           
        }
        
        if  ( !isset($fields) || empty($fields) )
        {
            $fields = [
                'D_Campaign.name AS campaign',
                'sum(F_ClusterLogs.imps) AS imps'
            ];

            $group[]  = 'D_Campaign.name';           

            $query->groupBy( 'D_Campaign.name' );
        }

        $query->select( $fields );


        // relations
        $query->rightJoin([
            'F_ClusterLogs ON (F_ClusterLogs.session_hash=F_CampaignLogs.session_hash)',
        ]);

        $query->leftJoin([
            'D_Placement ON ( F_ClusterLogs.D_Placement_id=D_Placement.id )',
        ]);
        
        $query->joinWith([
            'campaign',
        ]);
        
 
        // sorting
        $dataProvider->sort->attributes['campaign'] = [
            'asc' => ['D_Campaign.name' => SORT_ASC],
            'desc' => ['D_Campaign.name' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['affiliate'] = [
            'asc' => ['D_Campaign.Affiliates_name' => SORT_ASC],
            'desc' => ['D_Campaign.Affiliates_name' => SORT_DESC],
        ];  
        $dataProvider->sort->attributes['publisher'] = [
            'asc' => ['D_Placement.Publishers_name' => SORT_ASC],
            'desc' => ['D_Placement.Publishers_name' => SORT_DESC],
        ];   
        $dataProvider->sort->attributes['model'] = [
            'asc' => ['D_Placement.model' => SORT_ASC],
            'desc' => ['D_Placement.model' => SORT_DESC],
        ];               
        $dataProvider->sort->attributes['status'] = [
            'asc' => ['D_Placement.status' => SORT_ASC],
            'desc' => ['D_Placement.status' => SORT_DESC],
        ];            
        $dataProvider->sort->attributes['cluster'] = [
            'asc' => ['F_ClusterLogs.cluster_name' => SORT_ASC],
            'desc' => ['F_ClusterLogs.cluster_name' => SORT_DESC],
        ];        
        $dataProvider->sort->attributes['placement'] = [
            'asc' => ['D_Placement.name' => SORT_ASC],
            'desc' => ['D_Placement.name' => SORT_DESC],
        ];                
        $dataProvider->sort->attributes['country'] = [
            'asc' => ['F_ClusterLogs.country' => SORT_ASC],
            'desc' => ['F_ClusterLogs.country' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['connection_type'] = [
            'asc' => ['F_ClusterLogs.connection_type' => SORT_ASC],
            'desc' => ['F_ClusterLogs.connection_type' => SORT_DESC],
        ];        
        $dataProvider->sort->attributes['carrier'] = [
            'asc' => ['F_ClusterLogs.carrier' => SORT_ASC],
            'desc' => ['F_ClusterLogs.carrier' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['device'] = [
            'asc' => ['F_ClusterLogs.device' => SORT_ASC],
            'desc' => ['F_ClusterLogs.device' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['device_brand'] = [
            'asc' => ['F_ClusterLogs.device_brand' => SORT_ASC],
            'desc' => ['F_ClusterLogs.device_brand' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['device_model'] = [
            'asc' => ['F_ClusterLogs.device_model' => SORT_ASC],
            'desc' => ['F_ClusterLogs.device_model' => SORT_DESC],
        ];        
        $dataProvider->sort->attributes['os'] = [
            'asc' => ['F_ClusterLogs.os' => SORT_ASC],
            'desc' => ['F_ClusterLogs.os' => SORT_DESC],
        ];        
        $dataProvider->sort->attributes['os_version'] = [
            'asc' => ['F_ClusterLogs.os_version' => SORT_ASC],
            'desc' => ['F_ClusterLogs.os_version' => SORT_DESC],
        ];        
        $dataProvider->sort->attributes['browser'] = [
            'asc' => ['F_ClusterLogs.browser' => SORT_ASC],
            'desc' => ['F_ClusterLogs.browser' => SORT_DESC],
        ];        
        $dataProvider->sort->attributes['browser_version'] = [
            'asc' => ['F_ClusterLogs.browser_version' => SORT_ASC],
            'desc' => ['F_ClusterLogs.browser_version' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['cost'] = [
            'asc' => ['F_ClusterLogs.cost' => SORT_ASC],
            'desc' => ['F_ClusterLogs.cost' => SORT_DESC],
        ];        
        $dataProvider->sort->attributes['imps'] = [
            'asc' => ['F_ClusterLogs.imps' => SORT_ASC],
            'desc' => ['F_ClusterLogs.imps' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['clicks'] = [
            'asc' => ['count(F_CampaignLogs.click_time)' => SORT_ASC],
            'desc' => ['count(F_CampaignLogs.click_time)' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['convs'] = [
            'asc' => ['count(F_CampaignLogs.conv_time)' => SORT_ASC],
            'desc' => ['count(F_CampaignLogs.conv_time)' => SORT_DESC],
        ];                     


        // filters
        if ( isset($params['CampaignLogsSearch']['date_start']) )
            $dateStart = date( 'Y-m-d', strtotime($params['CampaignLogsSearch']['date_start']) );
        else
            $dateStart = date( 'Y-m-d' );

        if ( isset($params['CampaignLogsSearch']['date_end']) )
            $dateEnd= date( 'Y-m-d', strtotime($params['CampaignLogsSearch']['date_end']) );
        else
            $dateEnd = date( 'Y-m-d' );
      
        $query->andFilterWhere( ['>=', 'date(imp_time)', $dateStart] );
        $query->andFilterWhere( ['<=', 'date(imp_time)', $dateEnd] );


        if ( isset($params['publisher']) && $params['publisher'] ){
            $first = true;
            foreach ( $params['publisher'] as $id )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'D_Placement.Publishers_id', $id] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'D_Placement.Publishers_id', $id] );
                }
            }
        }

        if ( isset($params['affiliate']) && $params['affiliate'] ){
            $first = true;
            foreach ( $params['affiliate'] as $id )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'D_Campaign.Affiliates_id', $id] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'D_Campaign.Affiliates_id', $id] );
                }
            }
        }

        if ( isset($params['campaign']) && $params['campaign'] ){
            $first = true;
            foreach ( $params['campaign'] as $id )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'D_Campaign.id', $id] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'D_Campaign.id', $id] );
                }
            }
        }

        if ( isset($params['cluster']) && $params['cluster'] ){
            $first = true;
            foreach ( $params['cluster'] as $id )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'F_ClusterLogs.cluster_id', $id] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'F_ClusterLogs.cluster_id', $id] );
                }
            }
        }

        if ( isset($params['placement']) && $params['placement'] ){
            $first = true;
            foreach ( $params['placement'] as $id )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'F_ClusterLogs.D_Placement_id', $id] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'F_ClusterLogs.D_Placement_id', $id] );
                }
            }
        }        

        if ( isset($params['carrier']) && $params['carrier'] ){
            $first = true;
            foreach ( $params['carrier'] as $id )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'F_ClusterLogs.carrier', $id] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'F_ClusterLogs.carrier', $id] );
                }
            }
        }

        if ( isset($params['country']) && $params['country'] ){
            $first = true;
            foreach ( $params['country'] as $id )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'F_ClusterLogs.country', $id] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'F_ClusterLogs.country', $id] );
                }
            }
        }    

        if ( isset($params['device']) && $params['device'] ){
            $first = true;
            foreach ( $params['device'] as $id )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'F_ClusterLogs.device', $id] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'F_ClusterLogs.device', $id] );
                }
            }
        }              

        if ( isset($params['device_brand']) && $params['device_brand'] ){
            $first = true;
            foreach ( $params['device_brand'] as $id )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'F_ClusterLogs.device_brand', $id] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'F_ClusterLogs.device_brand', $id] );
                }
            }
        }          

        if ( isset($params['device_model']) && $params['device_model'] ){
            $first = true;
            foreach ( $params['device_model'] as $id )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'F_ClusterLogs.device_model', $id] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'F_ClusterLogs.device_model', $id] );
                }
            }
        }            

        if ( isset($params['os']) && $params['os'] ){
            $first = true;
            foreach ( $params['os'] as $id )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'F_ClusterLogs.os', $id] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'F_ClusterLogs.os', $id] );
                }
            }
        }      

        if ( isset($params['os_version']) && $params['os_version'] ){
            $first = true;
            foreach ( $params['os_version'] as $id )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'F_ClusterLogs.os_version', $id] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'F_ClusterLogs.os_version', $id] );
                }
            }
        }

        if ( isset($params['browser']) && $params['browser'] ){
            $first = true;
            foreach ( $params['browser'] as $id )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'F_ClusterLogs.browser', $id] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'F_ClusterLogs.browser', $id] );
                }
            }
        }            

        if ( isset($params['browser_version']) && $params['browser_version'] ){
            $first = true;
            foreach ( $params['browser_version'] as $id )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'F_ClusterLogs.browser_version', $id] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'F_ClusterLogs.browser_version', $id] );
                }
            }
        }    

        if ( isset($params['placement']) && $params['placement'] ){
            $first = true;
            foreach ( $params['placement'] as $id )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'D_Placement.id', $id] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'D_Placement.id', $id] );
                }
            }
        }    
                          
        //var_export( $query->createCommand()->getRawSql() );die();

        return $dataProvider;
    }

    public function searchTotals($params)
    {
        $query = CampaignLogs::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        // fields
        $fields = [];

        if ( isset($params['CampaignLogsSearch']['fields_group1']) && !empty( $params['CampaignLogsSearch']['fields_group1'] ) )
        {
            $filterFields = $params['CampaignLogsSearch']['fields_group1'];

            if ( isset($params['CampaignLogsSearch']['fields_group2']) && !empty( $params['CampaignLogsSearch']['fields_group2'] ) )            
                $filterFields = array_merge( $filterFields, $params['CampaignLogsSearch']['fields_group2'] );

            foreach ( $filterFields as $field )
            {
                switch ( $field )
                {
                    case 'imps':
                    case 'cost':
                        $fields[] = 'sum(F_ClusterLogs.'.$field.') AS '.$field;
                    break;
                    case 'revenue':
                        $fields[] = 'sum(F_CampaignLogs.'.$field.') AS '.$field;
                    break;
                    case 'clicks':
                        $fields[] = 'count(F_CampaignLogs.click_time) AS '.$field;
                    break;         
                    case 'convs':
                        $fields[] = 'count(F_CampaignLogs.conv_time) AS '.$field;
                    break;                                              
                }
            }         
        }
        
        if  ( !isset($fields) || empty($fields) )
        {
            $fields = [
                'sum(F_ClusterLogs.imps) AS imps'
            ];
        }

        $query->select( $fields );


        // relations
        $query->rightJoin([
            'F_ClusterLogs ON (F_ClusterLogs.session_hash=F_CampaignLogs.session_hash)',
        ]);

        $query->leftJoin([
            'D_Placement ON ( F_ClusterLogs.D_Placement_id=D_Placement.id )',
        ]);
        
        $query->joinWith([
            'campaign',
        ]);

        // filters
        if ( isset($params['CampaignLogsSearch']['date_start']) )
            $dateStart = date( 'Y-m-d', strtotime($params['CampaignLogsSearch']['date_start']) );
        else
            $dateStart = date( 'Y-m-d' );

        if ( isset($params['CampaignLogsSearch']['date_end']) )
            $dateEnd= date( 'Y-m-d', strtotime($params['CampaignLogsSearch']['date_end']) );
        else
            $dateEnd = date( 'Y-m-d' );
      
        $query->andFilterWhere( ['>=', 'date(imp_time)', $dateStart] );
        $query->andFilterWhere( ['<=', 'date(imp_time)', $dateEnd] );


        if ( isset($params['publisher']) && $params['publisher'] ){
            $first = true;
            foreach ( $params['publisher'] as $id )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'D_Placement.Publishers_id', $id] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'D_Placement.Publishers_id', $id] );
                }
            }
        }

        if ( isset($params['affiliate']) && $params['affiliate'] ){
            $first = true;
            foreach ( $params['affiliate'] as $id )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'D_Campaign.Affiliates_id', $id] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'D_Campaign.Affiliates_id', $id] );
                }
            }
        }

        if ( isset($params['campaign']) && $params['campaign'] ){
            $first = true;
            foreach ( $params['campaign'] as $id )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'D_Campaign.id', $id] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'D_Campaign.id', $id] );
                }
            }
        }

        if ( isset($params['cluster']) && $params['cluster'] ){
            $first = true;
            foreach ( $params['cluster'] as $id )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'F_ClusterLogs.cluster_id', $id] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'F_ClusterLogs.cluster_id', $id] );
                }
            }
        }

        if ( isset($params['placement']) && $params['placement'] ){
            $first = true;
            foreach ( $params['placement'] as $id )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'F_ClusterLogs.D_Placement_id', $id] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'F_ClusterLogs.D_Placement_id', $id] );
                }
            }
        }        

        if ( isset($params['carrier']) && $params['carrier'] ){
            $first = true;
            foreach ( $params['carrier'] as $id )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'F_ClusterLogs.carrier', $id] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'F_ClusterLogs.carrier', $id] );
                }
            }
        }

        if ( isset($params['country']) && $params['country'] ){
            $first = true;
            foreach ( $params['country'] as $id )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'F_ClusterLogs.country', $id] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'F_ClusterLogs.country', $id] );
                }
            }
        }    

        if ( isset($params['device']) && $params['device'] ){
            $first = true;
            foreach ( $params['device'] as $id )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'F_ClusterLogs.device', $id] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'F_ClusterLogs.device', $id] );
                }
            }
        }              

        if ( isset($params['device_brand']) && $params['device_brand'] ){
            $first = true;
            foreach ( $params['device_brand'] as $id )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'F_ClusterLogs.device_brand', $id] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'F_ClusterLogs.device_brand', $id] );
                }
            }
        }          

        if ( isset($params['device_model']) && $params['device_model'] ){
            $first = true;
            foreach ( $params['device_model'] as $id )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'F_ClusterLogs.device_model', $id] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'F_ClusterLogs.device_model', $id] );
                }
            }
        }            

        if ( isset($params['os']) && $params['os'] ){
            $first = true;
            foreach ( $params['os'] as $id )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'F_ClusterLogs.os', $id] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'F_ClusterLogs.os', $id] );
                }
            }
        }      

        if ( isset($params['os_version']) && $params['os_version'] ){
            $first = true;
            foreach ( $params['os_version'] as $id )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'F_ClusterLogs.os_version', $id] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'F_ClusterLogs.os_version', $id] );
                }
            }
        }

        if ( isset($params['browser']) && $params['browser'] ){
            $first = true;
            foreach ( $params['browser'] as $id )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'F_ClusterLogs.browser', $id] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'F_ClusterLogs.browser', $id] );
                }
            }
        }            

        if ( isset($params['browser_version']) && $params['browser_version'] ){
            $first = true;
            foreach ( $params['browser_version'] as $id )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'F_ClusterLogs.browser_version', $id] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'F_ClusterLogs.browser_version', $id] );
                }
            }
        }    

        if ( isset($params['placement']) && $params['placement'] ){
            $first = true;
            foreach ( $params['placement'] as $id )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'D_Placement.id', $id] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'D_Placement.id', $id] );
                }
            }
        }    
                          
        //var_export( $query->createCommand()->getRawSql() );die();

        return $dataProvider;
    }    
}
