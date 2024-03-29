<?php

namespace backend\controllers;

use Yii;
use app\models;
use app\models\Affiliates;
use app\models\AffiliatesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * AffiliatesController implements the CRUD actions for Affiliates model.
 */
class AffiliatesController extends Controller
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
     * Lists all Affiliates models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AffiliatesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Affiliates model.
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
     * Creates a new Affiliates model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Affiliates();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Affiliates model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        ini_set('memory_limit','3000M');
        set_time_limit(0);
                
        $model = $this->findModel($id);

        if ( $model )
        {
            $prevStatus = $model->status; 
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            if ( $prevStatus != 'paused'  &&  $model->status == 'paused' )
                $turnedOff = true;
            else
                $turnedOff = false;

            $campaigns = models\Campaigns::findAll( ['Affiliates_id' => $model->id] );

            $cache = new \Predis\Client( \Yii::$app->params['predisConString'] );

            foreach ( $campaigns as $campaign )
            {
                $cache->hmset( 'campaign:'.$campaign->id, [
                    'callback'      => $campaign->landing_url,
                    'ext_id'        => $campaign->ext_id,
                    'click_macro'   => $campaign->affiliates->click_macro,
                    'placeholders'  => $campaign->affiliates->placeholders,
                    'macros'        => $campaign->affiliates->macros,
                ]);    

                if ( $turnedOff )
                {
                    if ( $campaign->status != 'archived' )
                    {
                        $campaign->status = 'affiliate_off';
                        $campaign->save();                        
                    }

                    $clustersHasCampaigns = models\ClustersHasCampaigns::findAll( ['Campaigns_id' => $campaign->id] );

                    foreach ( $clustersHasCampaigns as $assign )
                    {
                        $packageIds = json_decode($campaign->app_id, true);
                        $clusterId = $assign['Clusters_id'];

                        if($campaign->unassignToCluster($clusterId))
                        {
                            if(isset($packageIds)){

                                foreach ( $packageIds as $packageId )
                                {
                                    $cache->zrem( 'clusterlist:'.$clusterId, $campaign->id.':'.$model->id.':'.$packageId );
                                }
                            } 
                        }                      
                    }

                    unset( $packageIds );
                    unset( $clustersHasCampaigns);
                }                            
            }   

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Affiliates model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->status = 'archived';
        $model->save();

        return $this->redirect(['index']);
    }


    /**
     * Finds the Affiliates model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Affiliates the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Affiliates::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionGetfilterlist($q=null){

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $list = AffiliatesSearch::searchForFilter($q);

        foreach ($list as $value) {
            $formatedList['results'][] = [
                'id'   => $value['id'],
                'text' => $value['name_id'],
                ];
        }

        return $formatedList;
    }
}
