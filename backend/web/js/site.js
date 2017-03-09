$(document).ready(function(){
	
	console.log('ready');

	var AdminLTEOptions = {
		//Define the set of colors to use globally around the website
		colors: {
			lightBlue: "#3c8dbc",
			red: "#f56954",
			green: "#00a65a",
			aqua: "#00c0ef",
			yellow: "#f39c12",
			blue: "#0073b7",
			navy: "#001F3F",
			teal: "#39CCCC",
			olive: "#3D9970",
			lime: "#01FF70",
			orange: "#FF851B",
			fuchsia: "#F012BE",
			purple: "#8E24AA",
			maroon: "#D81B60",
			black: "#222222",
			gray: "#d2d6de"
		},
	}

	onReady();
});

function onReady(){

	$('.grid-button').click(function(){
		$("iframe#control-sidebar").attr('src', $(this).attr('href'));
		console.log($(this).attr('href'));
	})
	$('.close-sidebar-button').click(function(){
		$("#currentGrid").yiiGridView("applyFilter");
		console.log("grid updated");
	})

}

