(function($) {
  $(window).on('load', function() {
    // This is being done on "load" since only at this time we have the
    // _dyid cookie available which will be required in the controller.
    var isDyIdCookieSet = false;
    var isDyIdServerCookieSet = false;
    $.each($.cookie(), function(key) {
      if (!isDyIdCookieSet && key.indexOf('_dyid') > -1) {
        isDyIdCookieSet = true;
      }
      if (!isDyIdServerCookieSet && key.indexOf('_dyid_server') > -1) {
        isDyIdServerCookieSet = true;
      }
      if (isDyIdCookieSet && isDyIdServerCookieSet) {
        // Break from loop.
        return false;
      }
    });

    if (!isDyIdCookieSet || isDyIdServerCookieSet) {
      return;
    }

    // Simply call the controller to set the cookie.
    $.ajax({
      type: 'POST',
      url: '/dyid',
    });
  });
})(jQuery);
