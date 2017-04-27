<?php

namespace backend\controllers;

use Yii;
use app\models\Placements;
use app\models\PlacementsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * PlacementsController implements the CRUD actions for Placements model.
 */
class PlacementsController extends Controller
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
     * Lists all Placements models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PlacementsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Placements model.
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
     * Creates a new Placements model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Placements();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            /*
            $cache->setMap( 'placement:'.$model->id,  [
                'frequency_cap'   => $model->frequency_cap,
                'payout'          => $model->payout,
                'model'           => $model->model,
                'cluster_id'      => 5,
                'cluster_name'    => 'Cluster 5',
                'status'          => 'health_check',
                'imps'            => 0,
                'size'            => '320x50'
            ]);            
            */
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Placements model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $cache = new \Predis\Client( \Yii::$app->params['predisConString'] );
            $cache->hmset( 'placement:'.$model->id,  [
                'frequency_cap'   => $model->frequency_cap,
                'payout'          => $model->payout,
                'model'           => $model->model,
                'status'          => $model->status,
                'imps'            => $model->imps,
                'size'            => $model->size
            ]);            
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Placements model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Placements model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Placements the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Placements::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
