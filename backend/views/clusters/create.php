<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Clusters */

$this->title = 'Create Clusters';
$this->params['breadcrumbs'][] = ['label' => 'Clusters', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="clusters-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
