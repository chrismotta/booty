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
        $start   = time();  
        ini_set('memory_limit','3000M');
        set_time_limit(0);
        
        $model        = new CampaignLogs();
        $searchModel  = new CampaignLogsSearch();

        $queryParams = Yii::$app->request->queryParams;

        if (isset($queryParams['CampaignLogsSearch'])) {
            $dataProvider = $searchModel->search($queryParams);
            $totalsProvider = $searchModel->searchTotals($queryParams);

            if ( isset($_REQUEST['download']) )
            {
                $this->_sendCsvFile( $dataProvider, $queryParams );
            }      
            else
            {
                return $this->render('index', [
                    'model'          => $model,
                    'searchModel'    => $searchModel,
                    'dataProvider'   => $dataProvider,
                    'totalsProvider' => $totalsProvider,
                    'startTime'      => $start
                ]);                
            }      
        } else {
            $dataProvider = null;
            $totalsProvider = null;

            return $this->render('index', [
                'model'          => $model,
                'searchModel'    => $searchModel,
                'dataProvider'   => $dataProvider,
                'totalsProvider' => $totalsProvider,
                'startTime'      => $start
            ]);            
        }
    }

    public function actionCsvdownload ($daysBefore=4) {

        $searchModel  = new CampaignLogsSearch();
        $dataProvider = $searchModel->searchCsv($daysBefore);
        
        // harcodear los fields según la función searchCsv y obtenerlos de ahí
        $fields = [];
        $this->_sendCsvFile( $dataProvider, 'autoreport_date.csv', $fields);

        // descargar el reporte en una carpeta y enviar un mail con el link de descarga
    }


    private function _sendCsvFile ( $dataProvider, $params )
    {
        if ( isset($params['CampaignLogsSearch']['date_start']) )
            $dateStart = date( 'Y-m-d', strtotime($params['CampaignLogsSearch']['date_start']) );
        else
            $dateStart = date( 'Y-m-d' );

        if ( isset($params['CampaignLogsSearch']['date_end']) )
            $dateEnd= date( 'Y-m-d', strtotime($params['CampaignLogsSearch']['date_end']) );
        else
            $dateEnd = date( 'Y-m-d' );

        $filename = 'Report_'.$dateStart.'-'.$dateEnd.'.csv';

        $fields = [];

        if ( isset($params['CampaignLogsSearch']['fields_group1']) && !empty( $params['CampaignLogsSearch']['fields_group1'] ) )
        {
            $fields = array_merge( $fields, $params['CampaignLogsSearch']['fields_group1'] );
        }

        if ( isset($params['CampaignLogsSearch']['fields_group2']) && !empty( $params['CampaignLogsSearch']['fields_group2'] ) )
        {
            $fields = array_merge( $fields, $params['CampaignLogsSearch']['fields_group2'] );
        }          

        if ( isset($params['CampaignLogsSearch']['fields_group3']) && !empty( $params['CampaignLogsSearch']['fields_group3'] ) )
        {
            $fields = array_merge( $fields, $params['CampaignLogsSearch']['fields_group3'] );        
        }

        $this->_getCsvFile($dataProvider, $filename, $fields);
    }


    private function _getCsvFile($dataProvider, $filename, $fields){

        $res      = $dataProvider->getModels();
        $header   = false;

        $fp       = fopen('php://output', 'w');

        header( "Content-type: text/csv;charset=utf-8");
        header( 'Content-Disposition: attachment;filename='.$filename);

        foreach( $dataProvider->getModels() as $data)
        {
            $data = (array)$data;

            $row = [];

            if (!$header)
                $headerFields = [];

            foreach ( $data as $field => $value )
            {                                         
                if ( in_array( $field, $fields ) )
                {
                    if(!$header)
                        $headerFields[] = strtoupper($field);

                    switch ( $field )
                    {
                        case 'campaign':
                        case 'affiliate':
                        case 'placement':
                        case 'publisher':
                        case 'cluster':
                            $idField = $field.'_id';
                            $row[]   = $value . ' ('.$data[$idField] .')';
                        break;
                        default:
                            $row[] = $value;
                        break;
                    }    
                }
                
            }  
            
            if ( !$header )
            {
                $header = true;
                fputcsv($fp, $headerFields, ',');
            }

            fputcsv($fp, $row, ',');
            unset( $row );
        }

        fclose($fp);
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
