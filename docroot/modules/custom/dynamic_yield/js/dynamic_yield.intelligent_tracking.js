(function ($) {
  $(window).on('load', function () {
    // This is being done on "load" since only at this time we have the
    // _dyid cookie available which will be required in the controller.
    var allCookies = $.cookie();
    var isDyIdCookieSet = '_dyid' in allCookies;
    var isDyIdServerCookieSet = '_dyid_server' in allCookies;

    // Return from here if both the cookies are available.
    if (isDyIdCookieSet && isDyIdServerCookieSet) {
      return;
    }

    // Update the local storage if dyid is empty in localstorage.
    var localStorageDyId = localStorage.getItem('_dyid');
    if (isDyIdCookieSet
      && !isDyIdServerCookieSet
      && !localStorageDyId) {
      localStorage.setItem('_dyid', isDyIdCookieSet);
      return;
    }

    // Simply call the controller to set the cookie.
    if (isDyIdCookieSet
      && !isDyIdServerCookieSet
      && localStorageDyId
      && localStorageDyId === allCookies['_dyid']) {
      $.post('/dyid.php');
      // Update the localstorage value.
      localStorage.setItem('_dyid', isDyIdCookieSet);
    }

  });
})(jQuery);
