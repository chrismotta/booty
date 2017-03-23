<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Placements */

$this->title = 'Create Placements';
$this->params['breadcrumbs'][] = ['label' => 'Placements', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="placements-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
