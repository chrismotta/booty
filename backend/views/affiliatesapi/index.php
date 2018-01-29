<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Affiliates API Management';
?>
<div class="box box-info">
    <div class="box-body">
    	<?php
		foreach ($model as $key => $value) {
			// var_dump( $value);
			
			$url = Url::to(['affiliatesapi/', 'affiliate_id' => $value->id]);
			echo Html::button($value->name, [
				'class' => 'btn btn-info',
				'style' => 'margin: 0px 10px 10px 0px',
				'onclick' => '
					$("#response").html("Requesting API data from '.$value->name.'...<hr>");
					$.post(
						"'.$url.'",
						function(data){
							// console.log(data)
							$("#response").html("'.$value->name.' API Request<hr>"+data);
						}
					)
				'
			]);
		}
		?>
	</div>
</div>
<div class="box">
    <div class="box-body" id="response">
		No API request sended.<hr>
	</div>
</div>