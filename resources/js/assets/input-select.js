// Forms Multi Select

$(document).ready(() => {
  setTimeout(function () {
    $(".multiselect-dropdown").select2({
      theme: "bootstrap4",
      placeholder: "-- Selecciona una opción --",
    });

    $("#example-single").multiselect({
      inheritClass: true,
    });

    $("#example-multi").multiselect({
      inheritClass: true,
    });

    $("#example-multi-check").multiselect({
      inheritClass: true,
    });

    $('#area_select').select2({
        theme: "bootstrap4",
        placeholder: "-- Selecciona el área --",
    });
  }, 0);
});
