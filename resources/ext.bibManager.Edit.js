addOnloadHook(bm_initCreateForm); //Register onload eventlistener. --> TODO: Maybe use jQuery in later MW versions?

function bm_initCreateForm() {
        var d = document;
        var entryTypeSelect = d.getElementById( 'bm_select_type' );
        if( entryTypeSelect ) {
                addHandler( entryTypeSelect, 'change', bm_entryTypeSelectChanged );
                d.getElementById('bm_select_entry_type_submit').style.display = 'none';
        }
}

function bm_entryTypeSelectChanged( e ) {
        e.target.form.submit();
}