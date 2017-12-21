<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\db\Expression;
use yii\data\ActiveDataProvider;
use backend\models\CampaignLogs;
use app\models\Affiliates;
// use common\models\User;

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
    public $fields_group3;
    public $column;
    public $imp_status;


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


    public function searchMediaBuyersReport($date = null, $daysBefore=4 )
    {
        $date = $date ? '"'.$date.'"' : 'CURDATE()';
        $query = CampaignLogs::find();

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => false,
        ]);

        $query->joinWith([
            'campaign',
        ]);

        $query->rightJoin([
            'F_ClusterLogs ON (F_ClusterLogs.session_hash=F_CampaignLogs.session_hash)',
        ]);

        $query->leftJoin([
            'D_Placement ON ( F_ClusterLogs.D_Placement_id=D_Placement.id )',
        ]);

        $query->select([
            'DATE(IF(conv_time is not null, conv_time, imp_time)) as date',  
            'F_ClusterLogs.D_Placement_id as placement_id', 
            'D_Placement.name as placement_name', 
            'pub_id',
            'subpub_id',
            'country',
            'os',
            'os_version',
            'connection_type',
            'carrier',
            'imp_status',
            'ceil(sum(if(clicks>0,imps/clicks,imps))) as imps',
            'count(conv_time) as convs',
            'sum(revenue) as revenue',
            'sum(if(clicks>0, cost/clicks, cost)) as cost',
            'sum(revenue) - sum(if(clicks>0, cost/clicks, cost)) as profit',
            ]);

        $query->groupBy([
            'DATE(IF(conv_time is not null, conv_time, imp_time))', 
            'cluster_id', 
            'F_ClusterLogs.D_Placement_id',
            'pub_id',
            'subpub_id',
            'country',
            'os',
            'os_version',
            'connection_type',
            'carrier',            
            'imp_status',
            ]);

        $query->where('DATE(IF(conv_time is not null, conv_time, imp_time)) >= SUBDATE('.$date.','.$daysBefore.')');

        return $dataProvider; 
    }

    public function searchCsv($daysBefore=4) {
        
        $date = isset($_GET['date']) ? '"'.$_GET['date'].'"' : 'CURDATE()';
        $query = CampaignLogs::find();

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => false,
        ]);

        $query->joinWith([
            'campaign',
        ]);

        $query->leftJoin([
            'Campaigns ON ( Campaigns.id=F_CampaignLogs.D_Campaign_id )',
        ]);        

        $query->rightJoin([
            'F_ClusterLogs ON (F_ClusterLogs.session_hash=F_CampaignLogs.session_hash)',
        ]);

        $query->leftJoin([
            'D_Placement ON ( F_ClusterLogs.D_Placement_id=D_Placement.id )',
        ]);

        $query->select([
            'DATE(IF(conv_time is not null, conv_time, imp_time)) as date', 
            'cluster_id',
            'F_ClusterLogs.cluster_name as cluster_name', 
            'D_Campaign.Affiliates_id as affiliate_id', 
            'Affiliates_name as affiliate_name', 
            'F_CampaignLogs.D_Campaign_id as campaign_id', 
            'D_Campaign.name as campaign_name',  
            'Publishers_id as publisher_id',
            'Publishers_name as publisher_name',  
            'F_ClusterLogs.D_Placement_id as placement_id', 
            'D_Placement.name as placement_name', 
            'pub_id',
            'subpub_id',
            'Campaigns.app_id as app_id',            
            'imp_status',
            'ceil(sum(if(clicks>0,imps/clicks,imps))) as imps',
            'count(click_id) as clicks',
            'count(conv_time) as convs',
            'sum(revenue) as revenue',
            'sum(if(clicks>0, cost/clicks, cost)) as cost',
            'sum(revenue) - sum(if(clicks>0, cost/clicks, cost)) as profit',
            ]);

        $query->groupBy([
            'DATE(IF(conv_time is not null, conv_time, imp_time))', 
            'cluster_id', 
            'D_Campaign.Affiliates_id', 
            'F_CampaignLogs.D_Campaign_id', 
            'Publishers_id', 
            'F_ClusterLogs.D_Placement_id',
            'pub_id',
            'subpub_id',
            'imp_status',
            ]);

        $query->where('DATE(IF(conv_time is not null, conv_time, imp_time)) >= SUBDATE('.$date.','.$daysBefore.')');

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

        $query = CampaignLogs::find();

        if ( isset($_REQUEST['download']) )
        {
            $dataProvider = new ActiveDataProvider([
                'query'      => $query,
                'pagination' => false,
            ]);
        }
        else
        {
            $dataProvider = new ActiveDataProvider([
                'query'      => $query,
            ]);
        }


        // role detection
        // $this->userroles = User::getRolesByID(Yii::$app->user->getId());
        $userroles = $this->userroles;
        //

        // relations

        $query->joinWith([
            'campaign',
        ]);

        $query->rightJoin([
            'F_ClusterLogs ON (F_ClusterLogs.session_hash=F_CampaignLogs.session_hash)',
        ]);

        $query->leftJoin([
            'D_Placement ON ( F_ClusterLogs.D_Placement_id=D_Placement.id )',
        ]);


        // fields
        $fields = [];
        $group  = [];
        $filterFields = [];

        if ( isset($params['CampaignLogsSearch']['fields_group1']) && !empty( $params['CampaignLogsSearch']['fields_group1'] ) )
            $filterFields = array_merge( $filterFields, $params['CampaignLogsSearch']['fields_group1'] );

        if ( isset($params['CampaignLogsSearch']['fields_group2']) && !empty( $params['CampaignLogsSearch']['fields_group2'] ) )            
            $filterFields = array_merge( $filterFields, $params['CampaignLogsSearch']['fields_group2'] );

        if ( isset($params['CampaignLogsSearch']['fields_group3']) && !empty( $params['CampaignLogsSearch']['fields_group3'] ) )            
            $filterFields = array_merge( $filterFields, $params['CampaignLogsSearch']['fields_group3'] );

        if ( !empty($filterFields) )
        {
            foreach ( $filterFields as $field )
            {
                switch ( $field )
                {
                    case 'date':
                        $fields[] = 'date(if(conv_time is not null, conv_time, imp_time)) AS date';
                        $group[]  = 'date(if(conv_time is not null, conv_time, imp_time))';
                    break;
                    case 'campaign':
                        $fields[] = 'D_Campaign.name AS campaign';
                        $fields[] = 'D_Campaign.id AS campaign_id';
                        $group[]  = 'D_Campaign.id';
                    break;
                    case 'affiliate':
                        $fields[] = 'D_Campaign.Affiliates_name AS affiliate';
                        $fields[] = 'D_Campaign.Affiliates_id AS affiliate_id';
                        $group[]  = 'D_Campaign.Affiliates_id';
                    break;
                    case 'publisher':
                        $fields[] = 'D_Placement.Publishers_name AS publisher';
                        $fields[] = 'D_Placement.Publishers_id AS publisher_id';
                        $group[]  = 'D_Placement.Publishers_id'; 
                    break;
                    case 'model':
                        $fields[] = 'D_Placement.model AS model';
                        $group[]  = 'D_Placement.model';
                    break;
                    case 'imps':
                        // when query groups by campaign or affiliate it could calculate with little discrepancy
                        $fields[] = 'ROUND(SUM(F_ClusterLogs.'.$field.'/F_ClusterLogs.clicks)) AS '.$field;
                    break;
                    case 'cost':
                        $fields[] = 'SUM(F_ClusterLogs.'.$field.'/F_ClusterLogs.clicks) AS '.$field;
                    break;
                    case 'unique_imps':
                        $fields[] = 'COUNT(F_ClusterLogs.session_hash) AS '.$field;
                    break;                    
                    case 'revenue':
                        $fields[] = 'SUM(F_CampaignLogs.'.$field.') AS '.$field;
                    break;
                    case 'clicks':
                        $fields[] = 'COUNT(F_CampaignLogs.click_time) AS '.$field;
                    break;         
                    case 'convs':
                        $fields[] = 'COUNT(F_CampaignLogs.conv_time) AS '.$field;
                    break;                                                   
                    case 'cluster':
                        $fields[] = 'F_ClusterLogs.cluster_name AS '.$field;
                        $fields[] = 'F_ClusterLogs.cluster_id AS cluster_id';
                        $group[]  = 'F_ClusterLogs.cluster_id';
                    break;    
                    case 'placement':
                        $fields[] = 'D_Placement.name AS '.$field;
                        $fields[] = 'D_Placement.id AS placement_id';
                        $group[]  = 'D_Placement.id';
                    break;  
                    case 'profit':
                        $fields[] = 'SUM(F_CampaignLogs.revenue)-SUM(F_ClusterLogs.cost/F_ClusterLogs.clicks) AS '.$field;
                    break;                      
                    case 'revenue_ecpm':
                        $fields[] = 'SUM(F_CampaignLogs.revenue) * 1000 / ROUND(SUM(F_ClusterLogs.imps/F_ClusterLogs.clicks)) AS '.$field;
                    break;
                    case 'cost_ecpm':
                        $fields[] = 'SUM(F_ClusterLogs.cost/F_ClusterLogs.clicks) * 1000 / ROUND(SUM(F_ClusterLogs.imps/F_ClusterLogs.clicks)) AS '.$field;
                    break;      
                    case 'profit_ecpm':
                        $fields[] = '(SUM(F_CampaignLogs.revenue)-SUM(F_ClusterLogs.cost/F_ClusterLogs.clicks)) * 1000 / ROUND(SUM(F_ClusterLogs.imps/F_ClusterLogs.clicks)) AS '.$field;
                    break; 
                    case 'conv_rate':
                        $fields[] = '(COUNT(F_CampaignLogs.conv_time)*100/ROUND(SUM(F_ClusterLogs.imps/F_ClusterLogs.clicks))) AS '.$field;
                    break; 
                    default:
                        $fields[] = 'F_ClusterLogs.'.$field.' AS '.$field;
                        $group[]  = 'F_ClusterLogs.'.$field;
                    break;
                }
            }
        }

        if  ( empty($fields) )
            $fields = [
                'D_Campaign.name AS campaign',
                'sum(F_ClusterLogs.imps) AS imps'
            ];

        if ( empty($group) )
            $group[]  = 'D_Campaign.id';                       

        $query->groupBy( $group );
        $query->select( $fields );
 
        // sorting
        $dataProvider->sort->attributes['date'] = [
            'asc' => ['date(F_ClusterLogs.imp_time)' => SORT_ASC],
            'desc' => ['date(F_ClusterLogs.imp_time)' => SORT_DESC],
        ];        
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
        $dataProvider->sort->attributes['imp_status'] = [
            'asc' => ['F_ClusterLogs.imp_status' => SORT_ASC],
            'desc' => ['F_ClusterLogs.imp_status' => SORT_DESC],
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
        $dataProvider->sort->attributes['pub_id'] = [
            'asc' => ['F_ClusterLogs.pub_id' => SORT_ASC],
            'desc' => ['F_ClusterLogs.pub_id' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['subpub_id'] = [
            'asc' => ['F_ClusterLogs.subpub_id' => SORT_ASC],
            'desc' => ['F_ClusterLogs.subpub_id' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['exchange_id'] = [
            'asc' => ['F_ClusterLogs.exchange_id' => SORT_ASC],
            'desc' => ['F_ClusterLogs.exchange_id' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['device_id'] = [
            'asc' => ['F_ClusterLogs.device_id' => SORT_ASC],
            'desc' => ['F_ClusterLogs.device_id' => SORT_DESC],
        ]; 
        $dataProvider->sort->attributes['profit'] = [
            'asc' => ['profit' => SORT_ASC],
            'desc' => ['profit' => SORT_DESC],
        ];                               
        $dataProvider->sort->attributes['revenue_ecpm'] = [
            'asc' => ['revenue_ecpm' => SORT_ASC],
            'desc' => ['revenue_ecpm' => SORT_DESC],
        ]; 
        $dataProvider->sort->attributes['cost_ecpm'] = [
            'asc' => ['cost_ecpm' => SORT_ASC],
            'desc' => ['cost_ecpm' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['profit_ecpm'] = [
            'asc' => ['profit_ecpm' => SORT_ASC],
            'desc' => ['profit_ecpm' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['conv_rate'] = [
            'asc' => ['conv_rate' => SORT_ASC],
            'desc' => ['conv_rate' => SORT_DESC],
        ];

        // role filter
        if(in_array('Advisor', $userroles)){
            $assignedPublishers = Publishers::getPublishersByUser(Yii::$app->user->getId());
            $query->andWhere( ['in', 'D_Placement.Publishers_id', $assignedPublishers] );
            // var_dump($assignedAffiliates);
            // die('advisor');
        } 
        // 
        
        if ( isset($params['CampaignLogsSearch']['date_start']) )
            $dateStart = date( 'Y-m-d', strtotime($params['CampaignLogsSearch']['date_start']) );
        else
            $dateStart = date( 'Y-m-d' );

        if ( isset($params['CampaignLogsSearch']['date_end']) )
            $dateEnd= date( 'Y-m-d', strtotime($params['CampaignLogsSearch']['date_end']) );
        else
            $dateEnd = date( 'Y-m-d' );
      
        $expression = new Expression('            
            date(if(conv_time is not null, conv_time, imp_time)) >= :date_start 
            AND 
            date(if(conv_time is not null, conv_time, imp_time)) <= :date_end             
        ', 
        [ 
            ':date_start' => $dateStart,
            ':date_end'   => $dateEnd
        ]);

        $query->andWhere( $expression );
        /*
        $query->andFilterWhere( ['>=', 'date(F_ClusterLogs.imp_time)', $dateStart] );
        $query->andFilterWHere( ['<=', 'date(F_ClusterLogs.imp_time)', $dateEnd] );
        */

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


        if ( isset($params['imp_status']) && $params['imp_status'] ){
            $first = true;
            foreach ( $params['imp_status'] as $value )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'F_ClusterLogs.imp_status', $value] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'F_ClusterLogs.imp_status', $value] );
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

        if ( isset($params['model']) && $params['model'] ){
            $first = true;
            foreach ( $params['model'] as $id )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'D_Placement.model', $id] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'D_Placement.model', $id] );
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
        $filterFields = [];

        if ( isset($params['CampaignLogsSearch']['fields_group1']) && !empty( $params['CampaignLogsSearch']['fields_group1'] ) )
            $filterFields = array_merge( $filterFields, $params['CampaignLogsSearch']['fields_group1'] );

        if ( isset($params['CampaignLogsSearch']['fields_group2']) && !empty( $params['CampaignLogsSearch']['fields_group2'] ) )            
            $filterFields = array_merge( $filterFields, $params['CampaignLogsSearch']['fields_group2'] );

        if ( isset($params['CampaignLogsSearch']['fields_group3']) && !empty( $params['CampaignLogsSearch']['fields_group3'] ) )            
            $filterFields = array_merge( $filterFields, $params['CampaignLogsSearch']['fields_group3'] );

        if ( !empty($filterFields) )
        {
            foreach ( $filterFields as $field )
            {
                switch ( $field )
                {
                    case 'imps':
                        // when query groups by campaign or affiliate it could calculate with little discrepancy
                        $fields[] = 'ROUND(SUM(F_ClusterLogs.'.$field.'/F_ClusterLogs.clicks)) AS '.$field;
                    break;                    
                    case 'cost':
                        $fields[] = 'sum(F_ClusterLogs.'.$field.'/F_ClusterLogs.clicks) AS '.$field;
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
                    case 'profit':
                        $fields[] = 'SUM(F_CampaignLogs.revenue)-SUM(F_ClusterLogs.cost/F_ClusterLogs.clicks) AS '.$field;
                    break;                      
                    case 'revenue_ecpm':
                        $fields[] = 'SUM(F_CampaignLogs.revenue) * 1000 / ROUND(SUM(F_ClusterLogs.imps/F_ClusterLogs.clicks)) AS '.$field;
                    break;
                    case 'cost_ecpm':
                        $fields[] = 'SUM(F_ClusterLogs.cost/F_ClusterLogs.clicks) * 1000 / ROUND(SUM(F_ClusterLogs.imps/F_ClusterLogs.clicks)) AS '.$field;
                    break;      
                    case 'profit_ecpm':
                        $fields[] = '(SUM(F_CampaignLogs.revenue)-SUM(F_ClusterLogs.cost/F_ClusterLogs.clicks)) * 1000 / ROUND(SUM(F_ClusterLogs.imps/F_ClusterLogs.clicks)) AS '.$field;
                    break; 
                    case 'conv_rate':
                        $fields[] = '(COUNT(F_CampaignLogs.conv_time)*100/ROUND(SUM(F_ClusterLogs.imps/F_ClusterLogs.clicks))) AS '.$field;
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
      
        $expression = new Expression('            
            date(if(conv_time is not null, conv_time, imp_time)) >= :date_start 
            AND 
            date(if(conv_time is not null, conv_time, imp_time)) <= :date_end 
        ', 
        [ 
            'date_start' => $dateStart,
            'date_end'   => $dateEnd
        ]);

        $query->andWhere( $expression );
        /*
        $query->andFilterWhere( ['>=', 'date(F_ClusterLogs.imp_time)', $dateStart] );
        $query->andFilterWHere( ['<=', 'date(F_ClusterLogs.imp_time)', $dateEnd] );
        */
       
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

        if ( isset($params['imp_status']) && $params['imp_status'] ){
            $first = true;
            foreach ( $params['imp_status'] as $value )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'F_ClusterLogs.imp_status', $value] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'F_ClusterLogs.imp_status', $value] );
                }
            }
        }          

        if ( isset($params['model']) && $params['model'] ){
            $first = true;
            foreach ( $params['model'] as $id )
            {
                if ( $first ){
                    $query->andFilterWhere( ['=', 'D_Placement.model', $id] );
                    $first = false;
                }
                else{
                    $query->orFilterWhere( ['=', 'D_Placement.model', $id] );
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

        return $dataProvider;
    }    
}
