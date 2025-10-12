// datatable cell padding
$('input[type=radio][name=table_cell_space_radio_btn]').change(function () {
    if (this.value == 'compact') {
        $("#pager").removeClass('wide-cell');
    } else {
        $("#pager").addClass('wide-cell');
    }
});