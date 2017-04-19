<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\grid\GridView;
use yii\jui\DatePicker;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use backend\models;
use yii\bootstrap;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\CampaignLogsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */


$this->registerJs(
   '$("document").ready(function(){ 
        $("#filters-form").on("pjax:end", function() {
            $.pjax.reload({container:"#results"});  //Reload GridView
        });
    });'
);
$this->title = 'Reporting';
$this->params['breadcrumbs'][] = $this->title;

$searchModel->date_start = isset($_GET['CampaignLogsSearch']['date_start']) ? $_GET['CampaignLogsSearch']['date_start'] : date( 'd-m-Y' );
$searchModel->date_end = isset($_GET['CampaignLogsSearch']['date_end']) ? $_GET['CampaignLogsSearch']['date_end'] : date( 'd-m-Y' );


$DPlacement       = models\DPlacement::find()->asArray()->all();
$DCampaign        = models\DCampaign::find()->asArray()->all();

$devices          = \Yii::$app->redis->smembers( 'devices' );
$deviceBrands     = \Yii::$app->redis->smembers( 'device_brands' );
$deviceModels     = \Yii::$app->redis->smembers( 'device_models' );
$os               = \Yii::$app->redis->smembers( 'os' );
$osVersions       = \Yii::$app->redis->smembers( 'os_versions' );
$browsers         = \Yii::$app->redis->smembers( 'browsers' );
$browserVersions  = \Yii::$app->redis->smembers( 'browser_versions' );
$countries        = \Yii::$app->redis->smembers( 'countries' );
$carriers         = \Yii::$app->redis->smembers( 'carriers' );

?>


<div class="campaign-logs-index">


<?php yii\widgets\Pjax::begin(['id' => 'filters-form']) ?>
<?php $form = ActiveForm::begin([
    'options' => ['data-pjax' => true ],
    'method'  => 'GET'
]); ?>

    <?=
        $form->field(
            $searchModel, 
            'date_start'
        )->widget(\yii\jui\DatePicker::classname(), [
        //'language' => 'ru',
        'dateFormat' => 'dd-MM-yyyy',
        ]);            
    ?>

    <?=
        $form->field(
            $searchModel, 
            'date_end'
        )->widget(\yii\jui\DatePicker::classname(), [
        //'language' => 'ru',
        'dateFormat' => 'dd-MM-yyyy',
        ]);            
    ?>


    <?=
        $form->field(
            $searchModel, 
            'publisher'
        )->widget( Select2::classname(), [
            'data' => ArrayHelper::map( 
                $DPlacement, 
                'Publishers_id', 
                'Publishers_name' 
            ),           
            'language' => 'us',
            'options' => [
                'placeholder' => 'Select a publisher ...', 
                'multiple' => true
            ],
            'pluginOptions' => [
                'tokenSeparators' => [' '],
                'maximumInputLength' => 50
            ],
        ]);            
    ?>

    <?=
        $form->field(
            $searchModel, 
            'affiliate'
        )->widget(Select2::classname(), [
            'data' => ArrayHelper::map( 
                $DCampaign, 
                'Affiliates_id', 
                'Affiliates_name' 
            ),
            'language' => 'us',
            'options' => ['placeholder' => 'Select an affiliate ...', 'multiple' => true],
            'pluginOptions' => [
                //'tags' => true,
                'tokenSeparators' => [',', ' '],
                'maximumInputLength' => 10
            ],
        ]);           
    ?>


    <?=
        $form->field(
            $searchModel, 
            'campaign'
        )->widget( Select2::classname(), [
            'data' => ArrayHelper::map( 
                $DCampaign, 
                'id', 
                'name' 
            ),                   
            'language' => 'us',
            'options' => [
                'options' => [
                    'style' => 'width:100px;height:200px;',
                ],
                'placeholder' => 'Select a campaign ...', 
                'multiple' => true
            ],
            'pluginOptions' => [
                //'tags' => true,
                'tokenSeparators' => [',', ' '],
                'maximumInputLength' => 10
            ],
        ]);           
    ?>


    <?=
        $form->field(
            $searchModel, 
            'cluster'
        )->widget(Select2::classname(), [
            'data' => [],
            'language' => 'us',
            'options' => ['placeholder' => 'Select a cluster ...', 'multiple' => true],
            'pluginOptions' => [
                //'tags' => true,
                'tokenSeparators' => [',', ' '],
                'maximumInputLength' => 10
            ],
        ]);           
    ?>


    <?=
        $form->field(
            $searchModel, 
            'placement'
        )->widget(Select2::classname(), [
            'data' => ArrayHelper::map( 
                $DPlacement, 
                'id', 
                'name' 
            ),         
            'language' => 'us',
            'options' => ['placeholder' => 'Select a placement ...', 'multiple' => true],
            'pluginOptions' => [
                //'tags' => true,
                'tokenSeparators' => [',', ' '],
                'maximumInputLength' => 10
            ],
        ]);           
    ?>


    <?=
        $form->field(
            $searchModel, 
            'country'
        )->widget(Select2::classname(), [
            'data' => $countries,
            'language' => 'us',
            'options' => ['placeholder' => 'Select a country ...', 'multiple' => true],
            'pluginOptions' => [
                //'tags' => true,
                'tokenSeparators' => [',', ' '],
                'maximumInputLength' => 10
            ],
        ]);           
    ?>

    <?=
        $form->field(
            $searchModel, 
            'carrier'
        )->widget(Select2::classname(), [
            'data' => $carriers,
            'language' => 'us',
            'options' => ['placeholder' => 'Select a carrier ...', 'multiple' => true],
            'pluginOptions' => [
                //'tags' => true,
                'tokenSeparators' => [',', ' '],
                'maximumInputLength' => 10
            ],
        ]);           
    ?>    

    <?=
        $form->field(
            $searchModel, 
            'device'
        )->widget(Select2::classname(), [
            'data' => $devices,      
            'language' => 'us',
            'options' => ['placeholder' => 'Select a device ...', 'multiple' => true],
            'pluginOptions' => [
                //'tags' => true,
                'tokenSeparators' => [',', ' '],
                'maximumInputLength' => 10
            ],
        ]);           
    ?> 

    <?=
        $form->field(
            $searchModel, 
            'device_brand'
        )->widget(Select2::classname(), [
            'data' => $deviceBrands,      
            'language' => 'us',
            'options' => ['placeholder' => 'Select a device brand ...', 'multiple' => true],
            'pluginOptions' => [
                //'tags' => true,
                'tokenSeparators' => [',', ' '],
                'maximumInputLength' => 10
            ],
        ]);           
    ?>  

    <?=
        $form->field(
            $searchModel, 
            'device_model'
        )->widget(Select2::classname(), [
            'data' => $deviceModels,      
            'language' => 'us',
            'options' => ['placeholder' => 'Select a device model ...', 'multiple' => true],
            'pluginOptions' => [
                //'tags' => true,
                'tokenSeparators' => [',', ' '],
                'maximumInputLength' => 10
            ],
        ]);           
    ?>  

    <?=
        $form->field(
            $searchModel, 
            'os'
        )->widget(Select2::classname(), [
            'data' => $os,      
            'language' => 'us',
            'options' => ['placeholder' => 'Select an os ...', 'multiple' => true],
            'pluginOptions' => [
                //'tags' => true,
                'tokenSeparators' => [',', ' '],
                'maximumInputLength' => 10
            ],
        ])->label('OS');           
    ?>  

    <?=
        $form->field(
            $searchModel, 
            'os_version'
        )->widget(Select2::classname(), [
            'data' => $osVersions,      
            'language' => 'us',
            'options' => ['placeholder' => 'Select an os version ...', 'multiple' => true],
            'pluginOptions' => [
                //'tags' => true,
                'tokenSeparators' => [',', ' '],
                'maximumInputLength' => 10
            ],
        ])->label('OS Version');           
    ?>  

    <?=
        $form->field(
            $searchModel, 
            'browser'
        )->widget(Select2::classname(), [
            'data' => $browsers,      
            'language' => 'us',
            'options' => ['placeholder' => 'Select a browser ...', 'multiple' => true],
            'pluginOptions' => [
                //'tags' => true,
                'tokenSeparators' => [',', ' '],
                'maximumInputLength' => 10
            ],
        ]);           
    ?>  

    <?=
        $form->field(
            $searchModel, 
            'browser_version'
        )->widget(Select2::classname(), [
            'data' => $browserVersions,      
            'language' => 'us',
            'options' => ['placeholder' => 'Select a browser version ...', 'multiple' => true],
            'pluginOptions' => [
                //'tags' => true,
                'tokenSeparators' => [',', ' '],
                'maximumInputLength' => 10
            ],
        ]);           
    ?>
 
    <?=
        bootstrap\ButtonGroup::widget([
                'buttons' => [
                    ['label' => 'A'],
                    ['label' => 'B'],
                    ['label' => 'C', 'visible' => false],
                ],
                'options' => [
                ]
            ]);    
    ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::submitButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>
 
<?php ActiveForm::end(); ?>
<?php yii\widgets\Pjax::end() ?>

</div>


<?php Pjax::begin( ['id' => 'results'] ); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],
            //'click_id',
            //'D_Campaign_id',
            //'session_hash',
            //'click_time',
            //'conv_time',
             /*
             [
             'attribute' => 'imp_time',
             'value' => 'clusterLog.imp_time'
             ],
             */
             [
             'attribute' => 'campaign',
             'value' => 'campaign.name'
             ],
             [
             'attribute' => 'affiliate',
             'value' => 'campaign.Affiliates_name'
             ],               
             [
             'attribute' => 'publisher',
             'value' => 'publisher'
             ],
             [
             'attribute' => 'model',
             'value' => 'model'
             ],
             [
             'attribute' => 'status',
             'value' => 'status'
             ],
             [
             'attribute' => 'country',
             'value' => 'clusterLog.country'
             ],
             [
             'attribute' => 'connection_type',
             'value' => 'clusterLog.connection_type'
             ],
             [
             'attribute' => 'carrier',
             'value' => 'clusterLog.carrier'
             ],
           
             [
             'attribute' => 'device',
             'value' => 'clusterLog.device'
             ],
             [
             'attribute' => 'device_brand',
             'value' => 'clusterLog.device_brand'
             ],             
             [
             'attribute' => 'device_model',
             'value' => 'clusterLog.device_model'
             ],
             [
             'attribute' => 'os',
             'value' => 'clusterLog.os'
             ],
             [
             'attribute' => 'os_version',
             'value' => 'clusterLog.os_version'
             ],
             [
             'attribute' => 'browser',
             'value' => 'clusterLog.browser'
             ],
             [
             'attribute' => 'browser_version',
             'value' => 'clusterLog.browser_version'
             ],
             [
             'attribute' => 'cost',
             'value' => 'clusterLog.cost'
             ],
             [
             'attribute' => 'imps',
             'value' => 'clusterLog.imps'
             ],
            'revenue',
            //['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
