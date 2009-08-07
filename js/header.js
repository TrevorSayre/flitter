// JavaScript Document
/*
$(document).ready( function() {
  $("#nav_bar li ul").hide();

  //$("#modal_iframe").hide();
  $("#nav_bar li").hover( function() { $("ul",this).show(); }, 
                          function() { $("ul",this).hide(); });
});
*/
$(document).ready( function() {
	
	var bannerWidth = $("#banner_menu").width();
	var width = 0 - bannerWidth - 32;
	var toggle = 0;

	$("#banner_end").click(function(){
		  if(toggle == 0) {
			toggle = 1;
		    /*$("#loginbar").animate({ 
		        marginRight: width + "px"
		      }, 500 );*/
		    $("#banner_menu").hide();
		  }
		  else {
			toggle = 0;
			$("#banner_menu").show();
			/*$("#loginbar").animate({ 
		        marginRight: "0px"
		    }, 500 );*/
		  }
	});

});
 