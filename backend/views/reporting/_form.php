<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use kartik\daterange\DateRangePicker;
use kartik\export\ExportMenu;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use backend\models;
use backend\components;
use yii\bootstrap;
/* @var $this yii\web\View */
/* @var $model backend\models\CampaignLogs */
/* @var $form yii\widgets\ActiveForm */

if ( isset($params['CampaignLogsSearch']['date_start']) )
    $dateStart = date( 'Y-m-d', strtotime($params['CampaignLogsSearch']['date_start']) );
else
    $dateStart = date( 'Y-m-d' );

if ( isset($params['CampaignLogsSearch']['date_end']) )
    $dateEnd= date( 'Y-m-d', strtotime($params['CampaignLogsSearch']['date_end']) );
else
    $dateEnd = date( 'Y-m-d' );

$r_publishers = [];
if ( isset($params['publisher']) && $params['publisher'] ){

    foreach ( $params['publisher'] as $id )
    {
        if ( !in_array($id, $r_publishers) )
            $r_publishers[] = $id;
    }
}

$r_affiliates = [];
if ( isset($params['affiliate']) && $params['affiliate'] ){

    foreach ( $params['affiliate'] as $id )
    {
        if ( !in_array($id, $r_affiliates) )
            $r_affiliates[] = (int)$id;
    }
}

$r_campaigns = [];
if ( isset($params['campaign']) && $params['campaign'] ){

    foreach ( $params['campaign'] as $id )
    {
        if ( !in_array($id, $r_campaigns) )
            $r_campaigns[] = $id;
    }
}

$r_carriers = [];
if ( isset($params['carrier']) && $params['carrier'] ){

    foreach ( $params['carrier'] as $id )
    {
        if ( !in_array($id, $r_carriers) )
            $r_carriers[] = $id;
    }
}

$r_clusters = [];
if ( isset($params['cluster']) && $params['cluster'] ){

    foreach ( $params['cluster'] as $id )
    {
        if ( !in_array($id, $r_clusters) )
            $r_clusters[] = $id;
    }
}


$r_placements = [];
if ( isset($params['placement']) && $params['placement'] ){

    foreach ( $params['placement'] as $id )
    {
        if ( !in_array($id, $r_placements) )
            $r_placements[] = $id;
    }
}

$r_countries = [];
if ( isset($params['country']) && $params['country'] ){

    foreach ( $params['country'] as $id )
    {
        if ( !in_array($id, $r_countries) )
            $r_countries[] = $id;
    }
}


$r_devices = [];
if ( isset($params['device']) && $params['device'] ){

    foreach ( $params['device'] as $id )
    {
        if ( !in_array($id, $r_devices) )
            $r_devices[] = $id;
    }
}


$r_devicebrands = [];
if ( isset($params['device_brand']) && $params['device_brand'] ){

    foreach ( $params['device_brand'] as $id )
    {
        if ( !in_array($id, $r_devicebrands) )
            $r_devicebrands[] = $id;
    }
}

$r_devicemodels = [];
if ( isset($params['device_model']) && $params['device_model'] ){

    foreach ( $params['device_model'] as $id )
    {
        if ( !in_array($id, $r_devicemodels) )
            $r_devicemodels[] = $id;
    }
}

$r_devicemodels = [];
if ( isset($params['device_model']) && $params['device_model'] ){

    foreach ( $params['device_model'] as $id )
    {
        if ( !in_array($id, $r_devicemodels) )
            $r_devicemodels[] = $id;
    }
}

$r_os = [];
if ( isset($params['os']) && $params['os'] ){

    foreach ( $params['os'] as $id )
    {
        if ( !in_array($id, $r_os) )
            $r_os[] = $id;
    }
}

$r_osversions = [];
if ( isset($params['os_version']) && $params['os_version'] ){

    foreach ( $params['os_version'] as $id )
    {
        if ( !in_array($id, $r_osversions) )
            $r_osversions[] = $id;
    }
}

$r_browsers = [];
if ( isset($params['browser']) && $params['browser'] ){

    foreach ( $params['browser'] as $id )
    {
        if ( !in_array($id, $r_browsers) )
            $r_browsers[] = $id;
    }
}

$r_browserversions = [];
if ( isset($params['browser_version']) && $params['browser_version'] ){

    foreach ( $params['browser_version'] as $id )
    {
        if ( !in_array($id, $r_browserversions) )
            $r_browserversions[] = $id;
    }
}


?>


<div class="campaign-logs-form">

    <?php //yii\widgets\Pjax::begin(['id' => 'filtersform']) ?>
    <?php $form = ActiveForm::begin([
        'options' => [
            'data-pjax' => true,
            'id' => 'filtersform'
        ],
        'method' => 'GET'
    ]); ?>

    <div class="box box-info">
    <div class="box-header">
        <h3 class="box-title">Date Range</h3>
        <div class="box-tools">
          <!-- This will cause the box to collapse when clicked -->
          <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
        </div>
      </div>
    <div class="box-body">

    <dir class="col-md-12">
    <?php
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
    ?>
    
    </dir>

    </div><!-- box body end -->
    </div><!-- box end -->

    <div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">Data Columns</h3>
        <div class="box-tools">
          <!-- This will cause the box to collapse when clicked -->
          <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
        </div>
      </div>
    <div class="box-body">
 
    <?= 
        $form->field($searchModel, 'fields_group1')->checkboxList(
            [
                'campaign'  => 'Campaign',
                'affiliate' => 'Affiliate',
                'placement' => 'Placement',
                'publisher' => 'Publisher',
                'cluster'   => 'Cluster',
                'model'     => 'Model',
                'status'    => 'Status',
                'imps'      => 'Imps',
                'clicks'    => 'Clicks',
                'convs'     => 'Convs',
                'cost'      => 'Cost',
                'revenue'   => 'Revenue'           
            ],
            [
                'item' => function ($index, $label, $name, $checked, $value) {
                    $class_btn = 'btn-default'; // Style for disable
                                   
                    if ( $checked )
                        $class_btn = 'btn-success'; // Style for checked button
    
                    return
                        '<label class="btn '. $class_btn.'" id="chkbut_'.$value.'">' . Html::checkbox($name, $checked, ['value' => $value, 'id'=>'chkinp_'.$value]) . $label . '</label>';
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
                        '<label class="btn '. $class_btn.'" id="chkbut_'.$value.'">' . Html::checkbox($name, $checked, ['value' => $value, 'id'=>'chkinp_'.$value]) . $label . '</label>';
                },
                'class' => 'btn-group', "data-toggle"=>"buttons", // Bootstrap class for Button Group
            ]
        )->label('');
    ?>

    </div><!-- box body end -->
    </div><!-- box end -->


    <div class="box box-info collapsed-box">
    <div class="box-header with-border">
        <h3 class="box-title">Data Filters</h3>
        <div class="box-tools">
          <!-- This will cause the box to collapse when clicked -->
          <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
        </div>
      </div>
    <div class="box-body">

    <dir class="col-md-6">

    <?= 
        '<label class="control-label">Publisher</label>';
        echo Select2::widget( [
            'name' => 'publisher',
            'data' => ArrayHelper::map( 
                $DPlacement, 
                'Publishers_id', 
                'Publishers_name' 
            ),
            'value' => $r_publishers,
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
        '<label class="control-label">Affiliate</label>';
        echo Select2::widget( [
            'name' => 'affiliate',
            'data' => ArrayHelper::map( 
                $DCampaign, 
                'Affiliates_id', 
                'Affiliates_name' 
            ),
            'value' => $r_affiliates,
            'changeOnReset' => true,
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
        '<label class="control-label">Campaign</label>';
        echo Select2::widget( [
            'name' => 'campaign',
            'data' => ArrayHelper::map( 
                $DCampaign, 
                'id', 
                'name' 
            ),               
            'value' => $r_campaigns,
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
        '<label class="control-label">Cluster</label>';
        echo Select2::widget( [
            'name' => 'cluster',
            'data' => $clusterNames,
            'value' => $r_clusters,
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
        '<label class="control-label">Placement</label>';
        echo Select2::widget( [
            'name' => 'placement',
            'data' => ArrayHelper::map( 
                $DPlacement, 
                'id', 
                'name' 
            ),         
            'value' => $r_placements,
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
        '<label class="control-label">Country</label>';
        echo Select2::widget( [
            'name' => 'country',
            'data' => components\MapHelper::arrayToMap( $countries ),
            'value' => $r_countries,
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
        '<label class="control-label">Carrier</label>';
        echo Select2::widget( [
            'name' => 'carrier',
            'data' => components\MapHelper::arrayToMap( $carriers ),
            'value' => $r_carriers,
            'language' => 'us',
            'options' => ['placeholder' => 'Select a carrier ...', 'multiple' => true],
            'pluginOptions' => [
                //'tags' => true,
                'tokenSeparators' => [',', ' '],
                'maximumInputLength' => 10
            ],
        ]);           
    ?>    

    </dir>
    <dir class="col-md-6">

    <?=
        '<label class="control-label">Device</label>';
        echo Select2::widget( [
            'name' => 'device',
            'data' => components\MapHelper::arrayToMap( $devices ),
            'value' => $r_devices,      
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
       '<label class="control-label">Device Brand</label>';
        echo Select2::widget( [
            'name' => 'device_brand',
            'data' => components\MapHelper::arrayToMap( $deviceBrands ),   
            'value' => $r_devicebrands,   
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
        '<label class="control-label">Device Model</label>';
        echo Select2::widget( [
            'name' => 'device_model',
            'data' => components\MapHelper::arrayToMap( $deviceModels ), 
            'value' => $r_devicemodels,    
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
        '<label class="control-label">OS</label>';
        echo Select2::widget( [
            'name' => 'os',
            'data' => components\MapHelper::arrayToMap( $os ),  
            'value' => $r_os,    
            'language' => 'us',
            'options' => ['placeholder' => 'Select an os ...', 'multiple' => true],
            'pluginOptions' => [
                //'tags' => true,
                'tokenSeparators' => [',', ' '],
                'maximumInputLength' => 10
            ],
        ]);           
    ?>  

    <?=
        '<label class="control-label">OS Version</label>';
        echo Select2::widget( [
            'name' => 'os_version',
            'data' => components\MapHelper::arrayToMap( $osVersions ), 
            'value' => $r_osversions,     
            'language' => 'us',
            'options' => ['placeholder' => 'Select an os version ...', 'multiple' => true],
            'pluginOptions' => [
                //'tags' => true,
                'tokenSeparators' => [',', ' '],
                'maximumInputLength' => 10
            ],
        ]);           
    ?>  

    <?=
        '<label class="control-label">Browser</label>';
        echo Select2::widget( [
            'name' => 'browser',
            'data' => components\MapHelper::arrayToMap( $browsers ), 
            'value' => $r_browsers,
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
        '<label class="control-label">Browser Version</label>';
        echo Select2::widget( [
            'name' => 'browser_version',
            'data' => components\MapHelper::arrayToMap( $browserVersions ),
            'value' => $r_browserversions,      
            'language' => 'us',
            'options' => ['placeholder' => 'Select a browser version ...', 'multiple' => true],
            'pluginOptions' => [
                //'tags' => true,
                'tokenSeparators' => [',', ' '],
                'maximumInputLength' => 10
            ],
        ]);           
    ?>

    </dir>

    </div><!-- box body end -->
    </div><!-- box end -->

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-info']) ?>
        <?= Html::resetInput('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>
    <?php //yii\widgets\Pjax::end() ?>

</div>
