<?php

namespace backend\controllers;

use Yii;
use backend\models\CampaignLogs;
use backend\models\CampaignLogsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\User;

/**
 * ReportingController implements the CRUD actions for CampaignLogs model.
 */
class ReportingController extends Controller
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
     * Lists all CampaignLogs models.
     * @return mixed
     */
    public function actionIndex()
    {
        ini_set('memory_limit','3000M');
        set_time_limit(0);

        
        $model        = new CampaignLogs();
        $searchModel  = new CampaignLogsSearch();
        $model->userroles = User::getRolesByID(Yii::$app->user->getId());

        $queryParams = Yii::$app->request->queryParams;

        if (isset($queryParams['CampaignLogsSearch'])) {
            $dataProvider = $searchModel->search($queryParams);
            $totalsProvider = $searchModel->searchTotals($queryParams);
        } else {
            $dataProvider = null;
            $totalsProvider = null;
        }

        return $this->render('index', [
            'model'          => $model,
            'searchModel'    => $searchModel,
            'dataProvider'   => $dataProvider,
            'totalsProvider' => $totalsProvider,
        ]);
    }

    /**
     * Displays a single CampaignLogs model.
     * @param string $id
     * @return mixed
     */
    /*
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }
    */
    /**
     * Creates a new CampaignLogs model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    /*
    public function actionCreate()
    {
        $model = new CampaignLogs();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->click_id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }
    */
    /**
     * Updates an existing CampaignLogs model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    /*
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->click_id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }
    */
    /**
     * Deletes an existing CampaignLogs model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
        /*
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
    */
    /**
     * Finds the CampaignLogs model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return CampaignLogs the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
        /*
    protected function findModel($id)
    {
        if (($model = CampaignLogs::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    */
}
