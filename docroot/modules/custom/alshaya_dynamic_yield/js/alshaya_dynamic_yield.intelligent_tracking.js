(function($) {
  $(window).on('load', function() {
    // This is being done on "load" since only at this time we have the
    // _dyid cookie available which will be required in the controller.
    var dyIdServerCookie = document.cookie.split(';')
      .find(function(row) {
        return row.match(/_dyid_server/);
      });

    if (typeof dyIdServerCookie !== 'undefined') {
      return;
    }

    // Simply call the controller to set the cookie.
    $.ajax({
      type: 'POST',
      url: '/dyid',
    });
  });
})(jQuery);
