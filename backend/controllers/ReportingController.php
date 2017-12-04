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
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => [ 'createautoreport', 'creatembautoreport', 'downloadmbautoreport' ],
                        'roles' => ['?']
                    ],                    
                    [
                        'allow' => false,
                        'roles' => ['?']
                    ],                      
                    [
                        'allow' => true,
                        'roles' => ['@']
                    ]                    
                ]
            ]

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

    public function actionCreateautoreport ($daysBefore=4, $test=false) {

        ini_set('memory_limit','3000M');
        set_time_limit(0);

        if ( isset($_GET['date']) )
        {
            $date     = $_GET['date'];
            $dateTime = date( 'Y-m-d H:i', strtotime($date) );            
        }
        else
        {
            $dateTime = date( 'Y-m-d H:i' );
            $date     = date( 'Y-m-d' );
        }

        $start    = time();
        $filename = './autoreport/autoreport_'.$date.'.csv';

        $searchModel  = new CampaignLogsSearch();
        $dataProvider = $searchModel->searchCsv($daysBefore);

        $fields = [
            'date',
            'cluster_id',
            'cluster_name',
            'affiliate_id',
            'affiliate_name',
            'campaign_id',
            'campaign_name',
            'publisher_id',
            'publisher_name',
            'placement_id',
            'placement_name',
            'pub_id',
            'subpub_id',
            'imp_status',
            'imps',
            'clicks',
            'convs',
            'revenue',
            'cost',
            'profit'
        ];

        $this->_getCsvFile( $dataProvider, $filename, $fields, false );

        $elapsed = $start - time();

        echo ( 
            'Datetime: '. $dateTime . '<br>' .
            'Filename: '. $filename . '<br>' .
            'Elapsed : '. $elapsed  . ' sec.'
        );

        if ( $test==1 )
            $to = 'dev@splad.co,apastor@splad.co';
        else
            $to = 'dev@splad.co,apastor@splad.co,mghio@splad.co,tgonzalez@splad.co';

        $this->_sendMail( 
            'Splad - Automatic Report<no-reply@spladx.co>', 
            $to,
            'AUTOMATIC REPORT '. $dateTime,
            '<html>
                <body>
                    <a href="http://cron.spladx.co/reporting/downloadautoreport?date='.$date.'">Download</a>
                </body>
            </html>'
        );
    }


    private function _isMediaBuyerPrefix ( $prefix )
    {
        switch ( strtolower($prefix) )
        {
            case 'ek_':
            case 'EK_':
                return 'tef@themedialab.co';
            break;
            case 'ga_':
            case 'GA_':
                return 'augusto@themedialab.co';
            break;
        }

        return false;
    }


    public function actionCreatembautoreport ( $daysBefore=4, $test=0 )
    {
        ini_set('memory_limit','3000M');
        set_time_limit(0);

        if ( isset($_GET['date']) )
        {
            $date     = $_GET['date'];
            $dateTime = date( 'Y-m-d H:i', strtotime($date) );            
        }
        else
        {
            $dateTime = date( 'Y-m-d H:i' );
            $date     = date( 'Y-m-d' );
        }
        
        $start    = time();
        $path     = './mbautoreport/';
        $filename = 'mbautoreport_'.$date.'.csv';

        $searchModel  = new CampaignLogsSearch();
        $dataProvider = $searchModel->searchMediaBuyersReport($date,$daysBefore);

        $fields = [
            'date',
            'placement',
            'pub_id',
            'subpub_id',
            'country',
            'os',
            'os_version',
            'connection_type',
            'carrier',            
            'imp_status',
            'imps',
            'convs',
            'revenue',
            'cost',
            'profit'
        ];

        $mbPrefixes = $this->_getMediaBuyerCsvFile( $dataProvider, $path, $filename, $fields, false );

        $elapsed = $start - time();

        echo ( 
            'Datetime: '. $dateTime . '<br>' .
            'Filename: '. $path.$filename . '<br>' .
            'Elapsed : '. $elapsed  . ' sec.'
        );

        $mbLinks = '';

        foreach ( $mbPrefixes AS $mbPrefix )
        {
            $url = "http://cron.spladx.co/reporting/downloadmbautoreport?prefix=".$date.':'.$mbPrefix;

            if ( $test!=1 )
            {                
                $this->_sendMail( 
                    'Splad - Automatic Report<no-reply@spladx.co>', 
                    $this->_isMediaBuyerPrefix( $mbPrefix ),
                    'MEDIA BUYER AUTOMATIC REPORT '. $dateTime,
                    '<html>
                        <body>
                        <strong>MEDIA BUYER AUTOMATIC REPORT '. $dateTime.'</strong>
                        <br>
                        <a href="'.$url.'">Download</a>
                        </body>
                    </html>'
                );    
            }

            $mbLinks .= '<a href="'.$url.'">Download '.strtoupper($mbPrefix).'</a><br>';
        }            

        if ( $test==1 )
            $to = 'dev@splad.co,apastor@splad.co';
        else
        {
            $to = 'dev@splad.co,apastor@splad.co,mghio@splad.co,tgonzalez@splad.co,pedro@themedialab.co,proman@splad.co';
        }

        $this->_sendMail( 
            'Splad - Automatic Report<no-reply@spladx.co>', 
            $to,
            'MEDIA BUYER AUTOMATIC REPORT '. $dateTime,
            '<html>
                <body>
                    <strong>Complete Report:</strong>
                    <br>
                    <a href="http://cron.spladx.co/reporting/downloadmbautoreport?date='.$date.'">Download</a>
                    <hr>
                    <strong>Media Buyers Reports:</strong>
                    <br>
                    '.$mbLinks.'
                </body>
            </html>'
        );        
    }


    public function actionDownloadmbautoreport ( $date = null, $prefix = null )
    {        
        if ( $prefix )
        {
            $parts = preg_split( '/(:)/', $prefix );

            $date   = $parts[0];
            $p      = $parts[1];
        }
        else
        {
            $date = $date ? $date : date( 'Y-m-d' );
            $p    = '';
        }

        $filename = strtolower($p).'mbautoreport_'.$date.'.csv';


        header( "Content-type: text/csv;charset=utf-8");
        header( 'Content-Disposition: render;filename='.$filename);

        echo file_get_contents('./mbautoreport/'.$filename);
    }


    public function actionDownloadautoreport ( $date = null )
    {
        $date = $date ? $date : date( 'Y-m-d' );
        $filename = 'autoreport_'.$date.'.csv';

        header( "Content-type: text/csv;charset=utf-8");
        header( 'Content-Disposition: render;filename='.$filename);

        echo file_get_contents('./autoreport/'.$filename);
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

        header( "Content-type: text/csv;charset=utf-8");
        header( 'Content-Disposition: attachment;filename='.$filename);        

        $this->_getCsvFile($dataProvider, $filename, $fields, true );
    }


    private function _sendmail ( $from, $to, $subject, $body )
    {
        $command = '
            export MAILTO="'.$to.'"
            export FROM="'.$from.'"
            export SUBJECT="'.$subject.'"
            export BODY="'.$body.'"
            (
             echo "From: $FROM"
             echo "To: $MAILTO"
             echo "Subject: $SUBJECT"
             echo "MIME-Version: 1.0"
             echo "Content-Type: text/html; charset=UTF-8"
             echo $BODY
            ) | /usr/sbin/sendmail -F $MAILTO -t -v -bm
        ';

        shell_exec( $command );
    }       


    private function _getCsvFile($dataProvider, $filename, $fields, $download = true ){

        $res      = $dataProvider->getModels();
        $header   = false;


        if ( $download )
            $fp       = fopen('php://output', 'w');
        else
            $fp       = fopen( $filename, 'w');

        foreach( $dataProvider->getModels() as $model )
        {
            $row = [];

            if (!$header)
                $headerFields = [];

            foreach ( $fields as $field )
            {                                         
                if(!$header)
                    $headerFields[] = $field;

                switch ( $field )
                {
                    case 'campaign':
                    case 'affiliate':
                    case 'cluster':
                        $idField = $field.'_id';
                        $row[]   = $model->$field . ' ('.$model->$idField .')';
                    break;
                    case 'publisher':
                    case 'placement':
                        $idField = $field.'_id';

                        if( in_array( 'Stakeholder', $model->userroles ) )
                            $row[] = $model->$idField;
                        else
                            $row[] = $model->$field . ' ('.$model->$idField.')';
                    break;
                    default:
                        $row[] = $model->$field;
                    break;
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


    private function _getMediaBuyerCsvFile($dataProvider, $path, $filename, $fields, $download = true ){

        $res      = $dataProvider->getModels();
        $header   = false;


        if ( $download )
            $fp       = fopen('php://output', 'w');
        else
            $fp       = fopen( $path.$filename, 'w');


        $mbFp     = [];
        $mbHeader = [];

        foreach( $dataProvider->getModels() as $model )
        {
            $row = [];

            if (!$header)
                $headerFields = [];

            foreach ( $fields as $field )
            {                                         
                if(!$header)
                {
                    switch ( $field )
                    {
                        case 'placement':
                            $headerFields[] = 'campaign';
                        break;
                        default:
                            $headerFields[] = $field;
                        break;
                    }                    
                }

                switch ( $field )
                {
                    case 'placement':
                        $row[] = $model->placement_name . ' ('.$model->placement_id.')';
                    break;
                    case 'pub_id':
                        $mbPrefix = strtolower(substr(stripslashes($model->$field), 0, 3 ));

                        $row[] = stripslashes($model->$field);
                    break;
                    case 'subpub_id':
                        $row[] = stripslashes($model->$field);
                    break;
                    default:
                        $row[] = $model->$field;
                    break;
                }    
            }  

            if ( !$header )
            {
                $header = true;
                fputcsv($fp, $headerFields, ',');
            }

            fputcsv($fp, $row, ',');

            if ( $mbPrefix && $this->_isMediaBuyerPrefix($mbPrefix)  )
            {
                if ( !isset($mbFp[$mbPrefix]) )
                {
                    $mbFp[$mbPrefix] = fopen( $path.$mbPrefix.$filename, 'w');
                    fputcsv($mbFp[$mbPrefix], $headerFields, ','); 
                }

                fputcsv($mbFp[$mbPrefix], $row, ',');               
            }

            unset( $row );
        }

        fclose($fp);

        $mbPrefixes = [];

        foreach ( $mbFp AS $mbPrefix => $fp )
        {            
            $mbPrefixes[] = $mbPrefix;
            fclose($fp);
        }

        return $mbPrefixes;
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
