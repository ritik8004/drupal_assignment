(function () {

  window.addEventListener('storage', function (event) {
    if (event.key !== 'cart_id') {
      return;
    }

    // Reload the page to ensure we have fresh cart info.
    window.location.href = window.location.href;
  });

})();
