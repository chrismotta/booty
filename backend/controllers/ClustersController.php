<?php

namespace backend\controllers;

use Yii;
use app\models\Clusters;
use app\models\ClustersSearch;
use app\models\Campaigns;
use app\models\CampaignsSearch;
use app\models\Countries;
use app\models\Carriers;
use app\models\ClustersHasCampaigns;
use yii\web\Controller;
use yii\filters\AccessControl;
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
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'create', 'update', 'view', 'assignment'],
                'rules' => [
                    // allow authenticated users
                    [
                        'allow' => true,
                        'roles' => ['Admin','Advisor'],
                    ],
                    // everything else is denied
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
                'carrier_list' => [], 
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
                'carrier_list' => Carriers::getListByCountry($model->country), 
            ]);
        }
    }

    public function actionGetcarrierlist($country=null){
        return json_encode(Carriers::getListByCountry($country));
    }

    /**
     * @param integer $id
     * @return mixed
     */
    public function actionAssignment($id)
    {
        $clustersModel = Clusters::findOne($id);

        $assignedModel = new CampaignsSearch();
        $assignedProvider = $assignedModel->searchAssigned(Yii::$app->request->queryParams, $id);

        return $this->render('assignment', [
            'assignedModel' => $assignedModel,
            'assignedProvider' => $assignedProvider,
            'clustersModel' => $clustersModel,
        ]);
    }

    /**
     * @param integer $id
     * @return mixed
     */
    public function actionAvailable($id)
    {
        $clustersModel = Clusters::findOne($id);

        $availableModel = new CampaignsSearch();
        $availableModel->country = $clustersModel->country;
        $availableModel->os = $clustersModel->os;
        $availableModel->connection_type = $clustersModel->connection_type;
        $availableModel->os_version = $clustersModel->os_version;
        $availableModel->device_type = $clustersModel->device_type;
        if(isset($clustersModel->Carriers_id))
            $availableModel->carrier = $clustersModel->carriers->carrier_name;

        $availableProvider = $availableModel->searchAvailable(Yii::$app->request->queryParams, $id);

        return $this->render('available', [
            'availableModel' => $availableModel,
            'availableProvider' => $availableProvider,
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
        $model = $this->findModel($id);
        $model->status = 'archived';
        $model->save();
        
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

    public function actionAssigncampaign($id, $debug=false){

        if(!isset($_GET['cid']))
            return 'cid parameter not set';

        $cid = json_decode($_GET['cid']);
        // var_export($cid);die();

        if(is_array($cid))
            $campaignList = $cid;
        else
            $campaignList[] = $cid;

        // init redis instance
        $cache = new \Predis\Client( \Yii::$app->params['predisConString'] );

        foreach ($campaignList as $campaignID) {

            $campaign = CampaignsSearch::findOne($campaignID);

            // if campaign exists and app_id is set and campaign is active
            if ( isset($campaign) && isset($campaign->app_id) && $campaign->status == 'active')
            {
                
                // if app_id is a json
                $packageIds = json_decode($campaign->app_id);

                if(isset($packageIds)){

                    if($campaign->assignToCluster($id)){

                        foreach ( $packageIds as $packageId )
                        {
                            $cache->zadd( 'clusterlist:'.$id, 1, $campaign->id.':'.$campaign->affiliates->id.':'.$packageId );
                        }

                        $return[] = $campaignID.': assignation ok';

                    }else{
                        $return[] = $campaignID.': assignation error';
                    }

                }else{

                    $return[] = $campaignID.': app_id format is not json';
                }

            }else{
                
                $return[] = $campaignID.': not an active campaign or app_id is not set';

            }

            $cache->hmset( 'campaign:'.$campaign->id, [
                'callback'      => $campaign->landing_url,
                'ext_id'        => $campaign->ext_id,
                'click_macro'   => $campaign->affiliates->click_macro,
                'placeholders'  => $campaign->affiliates->placeholders,
                'macros'        => $campaign->affiliates->macros
            ]);        

        }

        // debug
        if($debug)
            return var_export($return, true);

        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionUnassigncampaign($id, $debug=false){

        if(!isset($_GET['cid']))
            return 'cid parameter not set';

        $cid = json_decode($_GET['cid']);
        // var_export($cid);die();

        if(is_array($cid))
            $campaignList = $cid;
        else
            $campaignList[] = $cid;

        // init redis instance
        $cache = new \Predis\Client( \Yii::$app->params['predisConString'] );

        foreach ($campaignList as $campaignID) {

            $campaign = CampaignsSearch::findOne($campaignID);

            // if campaign exists
            if ( isset($campaign)){

                // if app_id is a json
                $packageIds = json_decode($campaign->app_id, true);

                if($campaign->unassignToCluster($id)){

                    if(isset($packageIds)){

                        foreach ( $packageIds as $packageId )
                        {
                            $cache->zrem( 'clusterlist:'.$id, $campaign->id.':'.$campaign->affiliates->id.':'.$packageId );
                        }

                    }

                    $return[] = $campaignID.': unassign ok';

                }else{
                    $return[] = $campaignID.': unassign error';
                }
            }else{
                
                $return[] = $campaignID.': not valid campaign';

            }

        }

        // debug
        if($debug)
            return var_export($return, true);

        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionChangefreq($Clusters_id){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $Campaigns_id = isset($_POST['editableKey']) ? $_POST['editableKey'] : null;
        $delivery_freq = isset($_POST['Campaigns'][$_POST['editableIndex']]['delivery_freq']) ? $_POST['Campaigns'][$_POST['editableIndex']]['delivery_freq'] : null;

        // debug //
        // $file = fopen("/home/chris/test/editable.txt","w");
        // fwrite($file, var_export($_POST['Campaigns'][$_POST['editableIndex']]['delivery_freq'], true));
        // fclose($file);
        
        if(!isset($Campaigns_id) or !isset($delivery_freq))
            return 'error:1';
        
        $chc = ClustersHasCampaigns::findOne([
            'Clusters_id' => $Clusters_id,
            'Campaigns_id' => $Campaigns_id,
        ]);

        if ( $chc->campaigns->app_id && ( $delivery_freq || $delivery_freq==0 ) )
        {
            $cache = new \Predis\Client( \Yii::$app->params['predisConString'] );

            $packageIds = json_decode($chc->campaigns->app_id);

            $prevDeliveryFreq = $chc->delivery_freq;

            if ( $chc->campaigns->status=='active' )
            {
                foreach ( $packageIds as $packageId )
                {
                    $cache->zadd( 'clusterlist:'.$Clusters_id, $delivery_freq, $chc->campaigns->id.':'.$chc->campaigns->affiliates->id.':'.$packageId );
                }
            }
        }

        if(!isset($chc))
            return 'error:2';

        $chc->delivery_freq = $delivery_freq;
        if($chc->save())
            return 'ok';
        else
            return 'error:3';
    }

    public function actionGetfilterlist($q=null){

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $list = ClustersSearch::searchForFilter($q);

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
}
