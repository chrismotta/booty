$(document).ready(function(){
	
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

	console.log('ready');
	// sidebarOpen();
	// sidebarClose();
});

function sidebarOpen(){
	$('.grid-button').click(function(event){
		// event.preventDefault();
		//$("iframe#control-sidebar").attr('src', $(this).attr('href'));
		console.log('open on sidebar: '+$(this).attr('href'));
		// event.stopPropagation();
		// return false;
	})
	$('.grid-button').attr('data-toggle', 'control-sidebar');
}

function sidebarClose(){
	$('.close-sidebar-button').click(function(){
		$("#currentGrid").yiiGridView("applyFilter");
		console.log("grid updated");
		/*$.pjax.reload({
			container: '#pjax-id', 
		}).done(function() { 
			sidebarOpen();
			console.log('load buttons again');
		});*/
	})
}

function openOnSidebar(e){
	console.log('open on sidebar: '+$(e).attr('href'));
	$("iframe#control-sidebar").attr('src', $(e).attr('href'));

}