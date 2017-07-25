<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Placements */

$this->title = 'Placement #'.$model->id.': '.$model->name;
$this->params['breadcrumbs'][] = ['label' => 'Placements', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="placements-view">

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>
    <p>
        <hr/>
        <h4>Iframe Tag</h4>
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
        ?>
        <?= Html::textarea('iframeTag', '<iframe src="'.$iframeSrc.'" frameborder="0" scrolling="no" width="'.$width.'" height="'.$height.'"></iframe>', ['class' => 'form-control']) ?>
        <h4>Javascript Tag</h4>
        <?= Html::textarea('javascriptTag', '<script type="text/javascript" src="'.$scriptSrc.'"></script>', ['class' => 'form-control']) ?>
        <hr/>
    </p>

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
