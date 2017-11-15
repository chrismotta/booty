<?php

namespace backend\controllers;

use Yii;
use app\models\PubidBlacklist;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * PubidblacklistController implements the CRUD actions for PubidBlacklist model.
 */
class PubidblacklistController extends Controller
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
     * Lists all PubidBlacklist models.
     * @return mixed
     */
    public function actionIndex()
    {
        \yii\helpers\Url::remember(['campaigns/']);

        $dataProvider = new ActiveDataProvider([
            'query' => PubidBlacklist::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single PubidBlacklist model.
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
     * Creates a new PubidBlacklist model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PubidBlacklist();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->Campaigns_id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing PubidBlacklist model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if(!isset($model)){
            $model = new PubidBlacklist();
            $model->Campaigns_id = $id;
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $cache  = new \Predis\Client( \Yii::$app->params['predisConString'] );

            $pubIds = preg_split( '/(\\n|\\r)/', $model->blacklist );
            $ids    = [];

            $cache->del( 'pubidblacklist:'.$model->Campaigns_id );

            foreach ( $pubIds as $pubId )
            {
                if ( !empty($pubId) )
                    $ids[] = $pubId;
            }

            if ( !empty($ids) )
                $cache->sadd( 'pubidblacklist:'.$model->Campaigns_id, $ids );

            $updated = true;
            
        } else {

            $updated = false;

        }

        $this->layout='iframe';
        return $this->render('update', [
            'model' => $model,
            'updated' => $updated,
        ]);
    }

    /**
     * Deletes an existing PubidBlacklist model.
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
     * Finds the PubidBlacklist model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PubidBlacklist the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PubidBlacklist::findOne($id)) !== null) {
            return $model;
        } else {
            // throw new NotFoundHttpException('The requested page does not exist.');
            return null;
        }
    }
}
