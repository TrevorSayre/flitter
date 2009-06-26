// JavaScript Document

$(document).ready( function() {
  $("#nav_bar li ul").hide();
  $("#nav_bar li").hover( function() { $("ul",this).show(); }, 
                          function() { $("ul",this).hide(); });
  
});
