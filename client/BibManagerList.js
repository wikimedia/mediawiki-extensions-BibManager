addOnloadHook(bm_initListForm); //Register onload eventlistener. --> TODO: Maybe use jQuery in later MW versions?

function bm_initListForm() {
        var d = document;
        var table = d.getElementById( 'bm_table' );
        var inputs = table.getElementsByTagName( 'input' );
        var exportheading = d.getElementById( 'bm_table_export_column_heading' );
        var exportallcheckbox = d.createElement( 'input' );
        exportallcheckbox.type = 'checkbox';
        exportallcheckbox.style.margin = '0 0 0 10px';
        exportheading.style.width = '75px';
        exportheading.appendChild( exportallcheckbox );

        bm_export_checkboxes = [];
        for( var i = 0; i < inputs.length; i++ ) {
                if( inputs[i].type == "checkbox" ) {
                        bm_export_checkboxes.push( inputs[i] );
                }
        }
    
        exportallcheckbox.onclick = function (e) {
                for( var j = 0; j < bm_export_checkboxes.length; j++ ){
                        bm_export_checkboxes[j].checked = this.checked;
                }
        }
}