<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\PubidBlacklist */

?>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title"><?= 'Campaign '.$id.' Blacklist' ?></h4>
</div>

<div class="modal-body">
        <?php $url = Url::to(['pubidblacklist/update', 'id' => $id]) ?>
        <iframe src="<?= $url ?>" frameborder=0 scrolling="no" width="570px" height="400px">
</div>
