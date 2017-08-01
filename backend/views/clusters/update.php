<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Clusters */

$this->title = 'Update Cluster: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Clusters', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="clusters-update">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
        'country_list' => $country_list,
    ]) ?>

</div>
