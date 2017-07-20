<?php

namespace backend\controllers;

use Yii;
use app\models\Clusters;
use app\models\ClustersSearch;
use app\models\Campaigns;
use app\models\CampaignsSearch;
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

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            $cache = new \Predis\Client( \Yii::$app->params['predisConString'] );
            $cache->hmset( 'placement:'.$model->placement->id,  [
                'cluster_id'      => $model->id,
                'cluster_name'    => $model->name,
            ]);

            $cache->hmset( 'cluster:'.$model->id,  [
                'country'           => strtolower($model->country),
                'os'                => $model->os,
                'connection_type'   => strtolower($model->connection_type), 
                'static_cp_land'    => $model->staticCampaigns->landing_url,
                'static_cp_300x250' => $model->staticCampaigns->creative_300x250,
                'static_cp_320x50'  => $model->staticCampaigns->creative_320x50 
            ]);


            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
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

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            $cache = new \Predis\Client( \Yii::$app->params['predisConString'] );
            $cache->hmset( 'placement:'.$model->placement->id,  [
                'cluster_id'      => $model->id,
                'cluster_name'    => $model->name,                
            ]);

            $cache->hmset( 'cluster:'.$model->id,  [
                'country'           => strtolower($model->country),
                'os'                => $model->os,
                'connection_type'   => strtolower($model->connection_type), 
                'static_cp_land'    => $model->staticCampaigns->landing_url,
                'static_cp_300x250' => $model->staticCampaigns->creative_300x250,
                'static_cp_320x50'  => $model->staticCampaigns->creative_320x50 
            ]);

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Clusters model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionAssignment($id)
    {
        $availableModel = new CampaignsSearch();
        $availableProvider = $availableModel->searchAvailable(Yii::$app->request->queryParams);

        $assignedModel = new CampaignsSearch();
        $assignedProvider = $assignedModel->searchAssigned($id);

        $clustersModel = Clusters::findOne($id);

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


        // debug
        echo $return;
    }

    public function actionUnassigncampaign($id){
        $campaignID = isset($_GET['cid']) ? $_GET['cid'] : null;

        $campaign = CampaignsSearch::findOne($campaignID);
        $return = $campaign->unassignToCluster($id);


        // debug
        echo $return;
    }
}
