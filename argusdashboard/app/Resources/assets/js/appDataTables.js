/** DataTables specific js**/

$('#sites-datatable, #contacts-datatable, #diseases-datatable, #thresholds-datatable')
    .on('draw.dt', function () {
        $('[data-toggle="tooltip"]').tooltip();
    });