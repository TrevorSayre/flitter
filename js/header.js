// JavaScript Document

$(document).ready( function() {
  $("#nav_bar li ul").hide();
  $('#modal_iframe').dialog({ bgiframe: true, modal:true, height:140 });  
  //$("#modal_iframe").hide();
  $("#nav_bar li").hover( function() { $("ul",this).show(); }, 
                          function() { $("ul",this).hide(); });
  $("#section_link_account_login").click( 
    function() {
      $.ajax( {
	      data:'state=start&method=ajax',
	      asnyc: false,
	      dataType:'text',
	      error:	function (XMLHttpRequest, textStatus, errorThrown) {
			  alert('Ajax Login Start State Failed:'+textStatus);
			},
	      success:  function (responseData, textStatus) {
			  alert('url: '+responseData);
			  //$('#modal_iframe').append('<iframe src="'+responseData+'"></iframe>');
			  
			  $('#modal_iframe').show();
			},
	      url:'http://flitter/php/twitter/twitter_login.php'});
      
      alert('I have been clicked');
      return false;
    }
  );


});
 