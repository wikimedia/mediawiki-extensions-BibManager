$(document).ready(function(){
	$('#bm_select_entry_type_submit').hide();
});

$(document).on( 'change', '#bm_select_type', function( e ) {
	e.target.form.submit();
});