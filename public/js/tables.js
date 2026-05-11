// Datatables

$(document).ready(() => {
    $("#dt_contracts").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": true,
        "searching": true,
        "ordering": false,
        "language": {
            "zeroRecords": "No se encontraron resultados",
            "search": "Buscar:",
            "lengthMenu":     "Mostrar _MENU_ registros",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            },
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty": "Mostrando 0 a 0 de 0 registros",
        }
    });
});
