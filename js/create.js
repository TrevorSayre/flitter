$(function() {
		$("#selectable").selectable();
		var selected = "";
		$("#createForm").submit(function () {
			$(".ui-selected").each( function () { 
				selected += $(this).attr("name")+",";
			});
			$("#selectedAccounts").val(selected);
		});
});