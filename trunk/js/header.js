// JavaScript Document

$(document).ready( function() {
  $("#nav_bar li ul").hide();

  //$("#modal_iframe").hide();
  $("#nav_bar li").hover( function() { $("ul",this).show(); }, 
                          function() { $("ul",this).hide(); });
});
 