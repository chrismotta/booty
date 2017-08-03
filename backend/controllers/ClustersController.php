<?php

namespace backend\controllers;

use Yii;
use app\models\Clusters;
use app\models\ClustersSearch;
use app\models\Campaigns;
use app\models\CampaignsSearch;
use app\models\Countries;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ClustersController implements the CRUD actions for Clusters model.
 */
class ClustersController extends Controller
{
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
     * Lists all Clusters models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ClustersSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Clusters model.
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
     * Creates a new Clusters model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {

        $model = new Clusters();
        $p = $model->load(Yii::$app->request->post());

        if ( !$model->connection_type || $model->connection_type=='' )
            $model->connection_type = null;

        if ( !$model->os || $model->os=='' )
            $model->os = null;

        if ( !$model->country || $model->country=='' )
            $model->country = null;

        $carrierName = $model->carriers ? $model->carriers->carrier_name : null;


        if ( $p  && $model->save()) {
            $cache = new \Predis\Client( \Yii::$app->params['predisConString'] );

            $cache->hmset( 'cluster:'.$model->id,  [
                'name'              => $model->name,
                'country'           => strtolower($model->country),
                'os'                => $model->os,
                'device_type'       => strtolower($model->device_type), 
                'connection_type'   => strtolower($model->connection_type), 
                'os_version'        => strtolower($model->os_version), 
                'carrier'           => strtolower($carrierName), 
                'static_cp_land'    => $model->staticCampaigns->landing_url,
                'static_cp_300x250' => $model->staticCampaigns->creative_300x250,
                'static_cp_320x50'  => $model->staticCampaigns->creative_320x50,
            ]);

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
                'country_list' => Countries::getList(), 
            ]);
        }
    }

    /**
     * Updates an existing Clusters model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $p = $model->load(Yii::$app->request->post());

        if ( !$model->connection_type || $model->connection_type=='' )
            $model->connection_type = null;

        if ( !$model->os || $model->os=='' )
            $model->os = null;

        if ( !$model->country || $model->country=='' )
            $model->country = null;

        $carrierName = $model->carriers ? $model->carriers->carrier_name : null;

        if ( $p && $model->save()) {

            $cache = new \Predis\Client( \Yii::$app->params['predisConString'] );

            $cache->hset( 'cluster:'.$model->id, 'name', $model->name );
            $cache->hset( 'cluster:'.$model->id, 'country', strtolower($model->country) );
            $cache->hset( 'cluster:'.$model->id, 'os', $model->os );
            $cache->hset( 'cluster:'.$model->id, 'connection_type', strtolower($model->connection_type) );
            $cache->hset( 'cluster:'.$model->id, 'carrier', strtolower($carrierName) );
            $cache->hset( 'cluster:'.$model->id, 'device_type', strtolower($model->device_type) );
            $cache->hset( 'cluster:'.$model->id, 'os_version', strtolower($model->os_version) );
            //$cache->hset( 'cluster:'.$model->id, 'carrier', strtolower($model->carriers->carrier_name) );
            $cache->hset( 'cluster:'.$model->id, 'static_cp_land', $model->staticCampaigns->landing_url );
            $cache->hset( 'cluster:'.$model->id, 'static_cp_300x250', $model->staticCampaigns->creative_300x250 );
            $cache->hset( 'cluster:'.$model->id, 'static_cp_320x50', $model->staticCampaigns->creative_320x50 );

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
                'country_list' => Countries::getList(), 
            ]);
        }
    }

    public function actionGetcarrierlist($country=null){
        return json_encode(Carriers::getListByCountry($country));
    }

    /**
     * Updates an existing Clusters model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionAssignment($id)
    {
        $clustersModel = Clusters::findOne($id);

        $availableModel = new CampaignsSearch();
        $availableModel->country = $clustersModel->country;
        $availableModel->os = $clustersModel->os;
        $availableModel->connection_type = $clustersModel->connection_type;

        $availableProvider = $availableModel->searchAvailable(Yii::$app->request->queryParams, $id);

        $assignedModel = new CampaignsSearch();
        $assignedProvider = $assignedModel->searchAssigned($id);

        return $this->render('assignment', [
            'availableModel' => $availableModel,
            'availableProvider' => $availableProvider,
            'assignedModel' => $assignedModel,
            'assignedProvider' => $assignedProvider,
            'clustersModel' => $clustersModel,
        ]);
    }

    /**
     * Deletes an existing Clusters model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        $cache = new \Predis\Client( \Yii::$app->params['predisConString'] );
        $cache->del( 'cluster:'.$id );
        $cache->del( 'clusterlist:'.$id );
        return $this->redirect(['index']);
    }

    /**
     * Finds the Clusters model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Clusters the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Clusters::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionAssigncampaign($id){
        $campaignID = isset($_GET['cid']) ? $_GET['cid'] : null;

        $campaign = CampaignsSearch::findOne($campaignID);
        $return = $campaign->assignToCluster($id);

        $cache = new \Predis\Client( \Yii::$app->params['predisConString'] );
        $cache->sadd( 'clusterlist:'.$id, $campaign->id );
        
        // debug
        // echo $return;
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionUnassigncampaign($id){
        $campaignID = isset($_GET['cid']) ? $_GET['cid'] : null;

        $campaign = CampaignsSearch::findOne($campaignID);
        $return = $campaign->unassignToCluster($id);

        $cache = new \Predis\Client( \Yii::$app->params['predisConString'] );
        $cache->srem( 'clusterlist:'.$id, $campaign->id );

        // debug
        // echo $return;
        return $this->redirect(Yii::$app->request->referrer);
    }
}
