<?php

namespace backend\controllers;

use Yii;
use app\models;
use app\models\Campaigns;
use app\models\CampaignsSearch;
use app\models\Countries;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * CampaignsController implements the CRUD actions for Campaigns model.
 */
class CampaignsController extends Controller
{
    protected $_blacklistAppId;
    protected $_blacklistKeyword;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Campaigns models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CampaignsSearch();
        $searchModel->status = 'active';
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionBytarget()
    {
        $searchModel = new CampaignsSearch();
        $dataProvider = $searchModel->searchByTarget(Yii::$app->request->queryParams);

        return $this->render('bytarget', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionBycluster()
    {
        $searchModel = new CampaignsSearch();
        $dataProvider = $searchModel->searchByCluster(Yii::$app->request->queryParams);

        return $this->render('bycluster', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Campaigns model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Campaigns model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Campaigns();
        $model->creation = 'manual';

        if ($model->load(Yii::$app->request->post()) && $model->save()) { 
            $cache = new \Predis\Client( \Yii::$app->params['predisConString'] );

            $cache->hmset( 'campaign:'.$model->id, [
                'callback'      => $model->landing_url,
                'ext_id'        => $model->ext_id,
                'click_macro'   => $model->affiliates->click_macro,
                'placeholders'  => $model->affiliates->placeholders,
                'macros'        => $model->affiliates->macros
            ]);            

            return $this->redirect(['view', 'id' => $model->id]);

        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Campaigns model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            $cache = new \Predis\Client( \Yii::$app->params['predisConString'] );

            $clustersHasCampaigns = models\ClustersHasCampaigns::findAll( ['Campaigns_id' => $model->id] );

            $cache->hmset( 'campaign:'.$model->id, [
                'callback'      => $model->landing_url,
                'ext_id'        => $model->ext_id,
                'click_macro'   => $model->affiliates->click_macro,
                'placeholders'  => $model->affiliates->placeholders,
                'macros'        => $model->affiliates->macros
            ]);  

            switch ( $model->status )
            {
                case 'active':
                    foreach ( $clustersHasCampaigns as $assign )
                    {
                        $packageIds = (array)json_decode($model->app_id);

                        foreach ( $packageIds AS $os => $packageId )
                        {                
                            $cache->zadd( 'clusterlist:'.$assign['Clusters_id'], $assign['delivery_freq'], $model->id.':'.$model->Affiliates_id.':'.$packageId );
                        }

                        // set campaign's cap in redis
                        if ( $model->daily_cap=='' )
                        {
                            $model->daily_cap = null;
                        }

                        if (
                            isset($model->daily_cap) 
                            && (int)$model->daily_cap>=0 )
                        {                            
                            $cache->zadd( 
                                'clustercaps:'.$assign['Clusters_id'], 
                                $model->daily_cap,
                                $model->id
                            );  
                        }
                        else if ( $model->aff_daily_cap && (int)$model->aff_daily_cap>=0 )
                        {
                            $cache->zadd( 
                                'clustercaps:'.$assign['Clusters_id'], 
                                $model->aff_daily_cap,
                                $model->id
                            );
                        }
                        else
                        {                           
                            $cache->zrem( 
                                'clustercaps:'.$assign['Clusters_id'], 
                                $model->id
                            );                 
                        }
                    }
                break;
                default:
                    $packageIds = (array)json_decode($model->app_id);

                    foreach ( $clustersHasCampaigns as $assign )
                    {
                        foreach ( $packageIds AS $os => $packageId )
                        {                
                            $cache->zrem( 'clusterlist:'.$assign['Clusters_id'], $model->id.':'.$model->Affiliates_id.':'.$packageId );
                        }

                        $cache->zrem( 
                            'clustercaps:'.$assign['Clusters_id'], 
                            $model->id
                        );                         
                    }
                break;
            }


   
            return $this->redirect(['view', 'id' => $model->id]);

        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Campaigns model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->status = 'archived';
        $model->save();

        $model->unlinkAll('clusters', true);

        $cache = new \Predis\Client( \Yii::$app->params['predisConString'] );
        $cache->del( 'campaign:'.$id );
        return $this->redirect(['index']);
    }

    /**
     * Finds the Campaigns model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Campaigns the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Campaigns::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionGetfilterlist($q=null){

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $list = CampaignsSearch::searchForFilter($q);

        foreach ($list as $value) {
            $formatedList['results'][] = [
                'id'   => $value['id'],
                'text' => $value['name_id'],
                ];
        }

        return $formatedList;
    }

    public function actionPubidblacklistiframe($id){

        return $this->renderPartial('pubidblacklistiframe', [
                'id' => $id,
            ]);
    }

     public function actionRestoreblacklisted($test=0){

        $this->_loadBlacklists();
        $campaigns = Campaigns::findAll(['status'=>'blacklisted']);
        $restored = 0;

        foreach ($campaigns as $key => $campaign) {

            $restore = true;
            if(isset($campaign->app_id)){

                $app_ids = json_decode($campaign->app_id);

                foreach ( $app_ids as $os => $app_id ){
                        
                    echo $os . ': ' . $app_id;
                    if ( $this->appIdIsBlacklisted( $app_id ) ){
                        echo ' :: Blacklisted by app_id';
                        $restore = false;
                    }
                    echo '<br/>';
                }

            }

            echo $campaign->name;
            if($this->hasBlacklistedKeyword( $campaign->name )){
                echo ' :: Blacklisted by keyword';
                $restore = false;
            }

            echo '<br/>';

            if($restore){
                if($test){
                    echo '======> TO BE RESTORED';
                }else{
                    $campaign->status = 'active';
                    $campaign->save();
                    echo '======> RESTORED';
                }
                $restored++;
            }

            echo '<hr/>';
        }

        echo '<hr/>';
        if($test)
            echo 'Campaigns to be restored: '.$restored;
        else
            echo 'Restored campaigns: '.$restored;
        echo '<hr/>';
    }

    public function hasBlacklistedKeyword( $string )
    {
        foreach ( $this->_blacklistKeyword as $keyword )
        {   
            if ( 
                preg_match ( 
                    "/(".strtolower($keyword->keyword).")/", strtolower($string) 
                )                
            )
            {
                return true;
            }
        }

        return false;
    }

    public function appIdIsBlacklisted( $app_id )
    {
        return in_array( $app_id, $this->_blacklistAppId );
    }

    private function _loadBlacklists()
    {
        $this->_blacklistKeyword = models\KeywordBlacklist::find()->all();
        $this->_blacklistAppId   = [];

        $appids = models\AppidBlacklist::find()->all();

        if ( $appids )
        {
            foreach ( $appids as $appid )
            {
                $this->_blacklistAppId[] = $appid->app_id;
            }                    
        }
    }
}
