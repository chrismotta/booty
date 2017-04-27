<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\grid\GridView;
use yii\jui\DatePicker;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use backend\models;
use backend\components;
use yii\bootstrap;
use kartik\daterange\DateRangePicker;
use kartik\export\ExportMenu;
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

$clusterNames     = components\MapHelper::redisToMap(\Yii::$app->redis->hgetall( 'clusternames' ));
$devices          = \Yii::$app->redis->smembers( 'devices' );
$deviceBrands     = \Yii::$app->redis->smembers( 'device_brands' );
$deviceModels     = \Yii::$app->redis->smembers( 'device_models' );
$os               = \Yii::$app->redis->smembers( 'os' );
$osVersions       = \Yii::$app->redis->smembers( 'os_versions' );
$browsers         = \Yii::$app->redis->smembers( 'browsers' );
$browserVersions  = \Yii::$app->redis->smembers( 'browser_versions' );
$countries        = \Yii::$app->redis->smembers( 'countries' );
$carriers         = \Yii::$app->redis->smembers( 'carriers' );

$params = Yii::$app->request->get();

if ( isset($params['CampaignLogsSearch']['fields_group1']) && !empty($params['CampaignLogsSearch']['fields_group1']) )
    $columns = $params['CampaignLogsSearch']['fields_group1'];
else
    $columns = ['campaign', 'imps'];
?>

<div class="campaign-logs-index">


<?php yii\widgets\Pjax::begin(['id' => 'filters-form']) ?>
<?php $form = ActiveForm::begin([
    'options' => ['data-pjax' => true ],
    'method'  => 'GET',
    'type' => ActiveForm::TYPE_HORIZONTAL,
]); ?>

    <?=
        '<label class="control-label">Date Range</label>';
        '<div class="input-group drp-container">';
        echo DateRangePicker::widget([
            'model'=>$searchModel,
            
            'attribute' => 'date_range',
            //'useWithAddon'=>true,
            'convertFormat'=>true,
            'startAttribute' => 'date_start',
            'endAttribute' => 'date_end',
            'pluginOptions'=>[
                'locale'=>[
                    'format' => 'd-m-Y',
                    'separator' => ' - '
                ],
            ]
        ]);
        echo '</div>';
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
            'data' => $clusterNames,
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
            'data' => components\MapHelper::arrayToMap( $countries ),
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
            'data' => components\MapHelper::arrayToMap( $carriers ),
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
            'data' => components\MapHelper::arrayToMap( $devices ),      
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
            'data' => components\MapHelper::arrayToMap( $deviceBrands ),      
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
            'data' => components\MapHelper::arrayToMap( $deviceModels ),      
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
            'data' => components\MapHelper::arrayToMap( $os ),      
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
            'data' => components\MapHelper::arrayToMap( $osVersions ),      
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
            'data' => components\MapHelper::arrayToMap( $browsers ),      
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
            'data' => components\MapHelper::arrayToMap( $browserVersions ),      
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
        $form->field($searchModel, 'fields_group1')->checkboxList(
            [
                'campaign'  => 'Campaign',
                'affiliate' => 'Affiliate',
                'publisher' => 'Publisher',
                'cluster'   => 'Cluster',
                'model'     => 'Model',
                'status'    => 'Status',
                'imps'      => 'Imps',
                'cost'      => 'Cost',
                'revenue'   => 'Revenue'           
            ],
            [
                'item' => function ($index, $label, $name, $checked, $value) {
                    $class_btn = 'btn-default'; // Style for disable
                                   
                    if ( $checked )
                        $class_btn = 'btn-success'; // Style for checked button
    
                    return
                        '<label class="btn '. $class_btn.'">' . Html::checkbox($name, $checked, ['value' => $value]) . $label . '</label>';
                },
                'class' => 'btn-group', "data-toggle"=>"buttons", // Bootstrap class for Button Group
            ]
        )->label('');
    ?>

    <?= 
        $form->field($searchModel, 'fields_group2')->checkboxList(
            [
                'country'         => 'Country',
                'connection_type' => 'Connection Type',
                'carrier'         => 'Carrier',
                'device'          => 'Device',
                'device_brand'    => 'Device Brand',
                'device_model'    => 'Device Model',
                'os'              => 'OS',
                'os_version'      => 'OS Version',
                'browser'         => 'Browser',
                'browser_version' => 'Browser Version'
            ],
            [
                'item' => function ($index, $label, $name, $checked, $value) {
                    if ( $checked )
                        $class_btn = 'btn-success'; // Style for checked button
                    else
                        $class_btn = 'btn-default'; // Style for disable
    
                    return
                        '<label class="btn '. $class_btn.'">' . Html::checkbox($name, $checked, ['value' => $value]) . $label . '</label>';
                },
                'class' => 'btn-group', "data-toggle"=>"buttons", // Bootstrap class for Button Group
            ]
        )->label('');
    ?>
    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::submitButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>
 
<?php ActiveForm::end(); ?>
<?php yii\widgets\Pjax::end() ?>


<div>

<?=
    ExportMenu::widget([
        'dataProvider' => $dataProvider,
        'columns' => $columns,
        'fontAwesome' => true,
        'exportConfig'  => [
            ExportMenu::FORMAT_EXCEL     => false,
        ]
    ]);
?>
</div>


<div style="overflow-x:scroll;">
<?php Pjax::begin( ['id' => 'results'] ); ?>    

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => $columns
    ]); ?>
<?php Pjax::end(); ?>
    
</div>
</div>
