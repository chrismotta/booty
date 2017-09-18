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
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
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

        if ($model->load(Yii::$app->request->post()) && $model->save()) { 
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

            switch ( $model->status )
            {
                case 'active':
                    foreach ( $clustersHasCampaigns as $assign )
                    {
                        $packageIds = (array)json_decode($campaign->app_id);

                        foreach ( $packageIds AS $os => $packageId )
                        {                
                            $cache->zadd( 'clusterlist:'.$assign['Clusters_id'], $assign['frequency'], $model->id.':'.$model->Affiliates_id.':'.$packageId );
                        }
                    }
                break;
                default:
                    foreach ( $clustersHasCampaigns as $assign )
                    {
                        $value = "[".$campaign->id.':'.$campaign->affiliates->id;
                        $cache->zremrangebylex( 'clusterlist:'.$id, $value, $value."\xff" );
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
}
