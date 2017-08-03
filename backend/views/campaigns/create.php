<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Campaigns */

$this->title = 'Create Campaign';
$this->params['breadcrumbs'][] = ['label' => 'Campaigns', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="campaigns-create">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
        'country_list' => $country_list,
        'carrier_list' => $carrier_list,
    ]) ?>

</div>
