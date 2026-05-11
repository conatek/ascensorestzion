// Perfect Scrollbar

$(document).ready(() => {
  setTimeout(function () {
    $(".scrollbar-container").each(function () {
      new PerfectScrollbar($(this)[0], {
        wheelSpeed: 2,
        wheelPropagation: false,
        minScrollbarLength: 20,
      });
    });

    if ($(".scrollbar-sidebar")[0]) {
      new PerfectScrollbar($(".scrollbar-sidebar")[0], {
        wheelSpeed: 2,
        wheelPropagation: true,
        minScrollbarLength: 20,
      });
    }
  }, 1000);
});
