<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\PubidBlacklist */

?>

<?php if(!$updated){ ?>

<?= $this->render('_form', [
    'model' => $model,
]) ?>

<?php }else{ ?>

	<div class="alert alert-info" role="alert">
	  <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true" style="margin-right:10px;font-size:24px;vertical-align: super;"></span> 
	  <span style="font-weight:bold; display:inline-block;">BLACKLIST CORRECTLY UPDATED<br/><a href="javascript:;" onClick="history.go(-1);return true;">GO BACK</a></span>
	</div>
<?php } ?>