(function ($, Drupal) {
  $(window).on('load', function () {
    // This is being done on "load" since only at this time we have the
    // _dyid cookie available which will be required in the controller.
    var allCookies = $.cookie();
    var isDyIdCookieSet = '_dyid' in allCookies;
    var isDyIdServerCookieSet = '_dyid_server' in allCookies;

    if (!isDyIdCookieSet || isDyIdServerCookieSet) {
      return;
    }

    // Simply call the controller to set the cookie.
    $.post(Drupal.url('dyid'));
  });
})(jQuery, Drupal);
