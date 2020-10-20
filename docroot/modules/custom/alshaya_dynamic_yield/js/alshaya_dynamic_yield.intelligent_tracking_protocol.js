(function($) {
  Drupal.behaviors.alshayaDynamicYieldItpBehavior = {
    attach: function (context, settings) {
      if (context == document) {
        var dyIdServerCookie = document.cookie.split(';')
          .find(function(row) {
            return row.match(/_dyid_server/);
          });

        if (typeof dyIdServerCookie !== 'undefined') {
          return;
        }

        $.ajax({
          type: 'POST',
          url: '/dyid',
        });
      }
    }
  }
}(jQuery))
