<?php

namespace backend\controllers;

use Yii;
use app\models\Placements;
use app\models\PlacementsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\User;

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

            $cache = new \Predis\Client( \Yii::$app->params['predisConString'] );
            $cache->hmset( 'placement:'.$model->id,  [
                'frequency_cap'   => $model->frequency_cap,
                'payout'          => $model->payout,
                'model'           => $model->model,
                'cluster_id'      => isset($model->clusters->id) ? $model->clusters->id : null,
                'status'          => $model->status,
                'size'            => $model->size,
                'imps'            => (int)$model->imps,
                'health_check_imps' => (int)$model->health_check_imps
            ]);
  
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
    public function actionUpdateonside($id)
    {
        $this->layout = 'iframe';
        return $this->actionUpdate($id);
    }
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $p = $model->load(Yii::$app->request->post());

        if ( $model->status=='health_check' )
            $model->imps = 0;

        if ( $p && $model->save()) {

            $cache = new \Predis\Client( \Yii::$app->params['predisConString'] );

            $cache->hset( 'placement:'.$model->id, 'frequency_cap', $model->frequency_cap );
            $cache->hset( 'placement:'.$model->id, 'payout', $model->payout );
            $cache->hset( 'placement:'.$model->id, 'model', $model->model );
            $cache->hset( 'placement:'.$model->id, 'status', $model->status );
            $cluster_id = isset($model->clusters->id) ? $model->clusters->id : null;
            $cache->hset( 'placement:'.$model->id, 'cluster_id', $cluster_id );
            // $cache->hset( 'placement:'.$model->id, 'cluster_name', $model->clusters->name );
            $cache->hset( 'placement:'.$model->id, 'size', $model->size );
            $cache->hset( 'placement:'.$model->id, 'imps', (int)$model->imps );
            $cache->hset( 'placement:'.$model->id, 'health_check_imps', (int)$model->health_check_imps );
         
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
        $model = $this->findModel($id);
        $model->status = 'archived';
        $model->save();

        $cache = new \Predis\Client( \Yii::$app->params['predisConString'] );
        $cache->del( 'placement:'.$id );

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

    public function actionGetfilterlist($q=null){

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $list = PlacementsSearch::searchForFilter($q);

        $userroles = User::getRolesByID(Yii::$app->user->getId());
        
        foreach ($list as $value) {
            $formatedList['results'][] = [
                'id'   => $value['id'],
                'text' => in_array('Stakeholder', $userroles) ? $value['id'] : $value['name_id'],
                ];
        }

        return $formatedList;
    }
}
