(function($) {
  $(window).on('load', function() {
    // This is being done on "load" since only at this time we have the
    // _dyid cookie available which will be required in the controller.
    var dyIdCookie = document.cookie.split(';')
      .find(function(row) {
        return row.match(/_dyid_server|_dyid/);
      });

    if ((typeof dyIdCookie !== 'undefined')
      && ((dyIdCookie.indexOf('_dyid') < 0)
      || (dyIdCookie.indexOf('_dyid_server') > -1))
    ) {
      return;
    }

    // Simply call the controller to set the cookie.
    $.ajax({
      type: 'POST',
      url: '/dyid',
    });
  });
})(jQuery);
