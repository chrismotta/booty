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
	sidebarOpen();
	sidebarClose();
	sidebarActive=true;
	deepLink();

});

function deepLink(){
	$('.deep-link').click(function(event){
		console.log( 'Key: '+$(this).attr('data-key') );
		console.log( 'Child: '+$(this).attr('data-child') );
		window.location.href = $(this).attr('data-child');
	});
}

function sidebarOpen(){
	$('.grid-button').click(function(event){

		$("iframe#control-sidebar").attr('src', $(this).attr('href'));
		console.log('open on sidebar: '+$(this).attr('href'));

		// fix the problem with ajax and sidebar
		if(sidebarActive){
			sidebarActive=false;
		}else{
			$.AdminLTE.controlSidebar.activate();
		}

		event.stopPropagation();
	})
	$('.grid-button').attr('data-toggle', 'control-sidebar');
}

function sidebarClose(){
	$('.close-sidebar-button').click(function(){
		console.log("grid updated");

		$.pjax.reload({
			container: '#pjax-id', 
		}).done(function() { 
			console.log('load buttons again');
			sidebarOpen();
			// sidebarClose();
			deepLink();
			$.AdminLTE.controlSidebar.activate();
		});
	})
}

function openOnSidebar(e){
	console.log('open on sidebar: '+$(e).attr('data-href'));
	// $.AdminLTE.controlSidebar.activate();

	// $.AdminLTE.controlSidebar.open(".control-sidebar");
	$("iframe#control-sidebar").attr('src', $(e).attr('data-href'));
	// e.preventDefault();
	// return false;
}