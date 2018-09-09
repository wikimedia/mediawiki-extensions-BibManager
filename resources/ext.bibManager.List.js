$( function () {
	var d = document;
	var table = d.getElementById( 'bm_table' );
	var inputs = table.getElementsByTagName( 'input' );
	var bm_export_checkboxes;

	// Get the checkbox
	var exportAllCheckbox = $(".bm-list-table-export-checkbox input");

	bm_export_checkboxes = [];
	for( var i = 0; i < inputs.length; i++ ) {
		if( inputs[i].type === "checkbox" ) {
			bm_export_checkboxes.push( inputs[i] );
		}
	}

	exportAllCheckbox.click( function() {
		for( var j = 0; j < bm_export_checkboxes.length; j++ ){
			bm_export_checkboxes[j].checked = this.checked;
		}
	});
} );