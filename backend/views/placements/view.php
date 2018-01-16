<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $model app\models\Placements */

$this->title = 'Placement #'.$model->id.': '.$model->name;
$this->params['breadcrumbs'][] = ['label' => 'Publishers', 'url' => ['/publishers']];
$this->params['breadcrumbs'][] = ['label' => 'Placements', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="placements-view">

        
        <?= Html::a('Admin', ['placements/'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>

    <br/><br/>

    <?php

    $adDomain = "//ad.spdx.co/";
    $iframeSrc = $adDomain . $model->id . '/';
    $scriptSrc = $adDomain . 'js/' . $model->id . '/';
    switch ($model->size) {
        case '300x250':
            $width = "300";
            $height = "250";
            break;
        case '320x50':
            $width = "320";
            $height = "50";
            break;
        default:
            $width = "";
            $height = "";
            break;
    }

    $macros = [
        'exchange_id' => '{YOUR_EXCHANGE_ID_MACRO_HERE}',
        'pub_id'      => '{YOUR_PUB_ID_MACRO_HERE}',
        'subpub_id'   => '{YOUR_SUBPUB_ID_MACRO_HERE}',
        'device_id'   => '{YOUR_DEVICE_ID_MACRO_HERE}',
    ];
    $qs_macros = urldecode(http_build_query($macros));

    $labels = 'MACROS: ';
    foreach ($macros as $key => $value) {
        $labels .= '<span class="label label-info">'.$key.'</span> ';
    }


    $availableDomains = [
        '//ad.spdx.co' => 'spdx.co (Default)',
        // 'https://ad.spdx.co' => 'spdx.co (Secure)',
        'http://ad.spdx.co' => 'spdx.co (Non Secure)',
        'http://ad.bigwo.co' => 'bigwo.co (Non Secure)',
        'http://ad.wigbo.co' => 'wigbo.co (Non Secure)',
    ];
    $defaultDomain = ['//ad.spdx.co'];

    $this->registerJs(
        '
        $(".change-domain-iframe").change(function(){
            
            var content = \'<iframe src="\' + this.value + \'?'.$qs_macros.'" frameborder="0" scrolling="no" width="'.$width.'" height="'.$height.'"></iframe>\';

            $("#domain-iframe").val(content);

            //console.log(content);
        });

        $(".change-domain-script").change(function(){
            
            var content = \'<script type="text\/javascript" src="\' + this.value + \'?'.$qs_macros.'" ><\/script >\';

            $("#domain-script").val(content);

            console.log(content);
        });
        ',
        View::POS_READY,
        'change-domain'
    );
    ?>

    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title">Iframe Tag</h3>
            <?= Html::dropDownList('iframe_domain', $defaultDomain, $availableDomains, [
                'style' => 'float:right',
                'class' => 'change-domain-iframe',
                ]) ?>
        </div>
        <div class="box-body">
        <?= Html::textarea('iframeTag', '<iframe src="'.$iframeSrc.'?'.$qs_macros.'" frameborder="0" scrolling="no" width="'.$width.'" height="'.$height.'"></iframe>', ['class' => 'form-control', 'disabled'=>'disabled', 'style'=>'cursor: text', 'id' => 'domain-iframe']) ?>
        </div>
        <div class="box-footer">
            <?= $labels ?>
        </div>
    </div>

    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title">Javascript Tag</h3>
            <?= Html::dropDownList('domain', $defaultDomain, $availableDomains, [
                'style' => 'float:right',
                'class' => 'change-domain-script',
                ]) ?>
        </div>
        <div class="box-body">
        <?= Html::textarea('javascriptTag', '<script type="text/javascript" src="'.$scriptSrc.'?'.$qs_macros.'"></script>', ['class' => 'form-control', 'disabled'=>'disabled', 'style'=>'cursor: text', 'id' => 'domain-script']) ?>
        </div>
        <div class="box-footer">
            <?= $labels ?>
        </div>
    </div>


    <div class="box box-info">
        <div class="box-body">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'label'     => 'Publisher',
                'attribute' => 'publishers.name'
            ],
            [
                'label'     => 'Cluster',
                'attribute' => 'clusters.name'
            ],            
            'name',
            'frequency_cap',
            'payout',
            'model',
            'status',
            'size',
            'health_check_imps',
        ],
    ]) ?>
        </div>
    </div>

</div>
