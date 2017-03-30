(function ($) {
  Drupal.behaviors.acq_cart_js = {
    attach: function (context, settings) {
      $.ajax({
        url: "/" + drupalSettings.path.pathPrefix + "mini-cart",
          success: function(result) {
            $("#mini-cart-wrapper").html(result);
          }
      });

      $(".acq-cart-summary .content").accordion({
        collapsible: true
      });
    }
  };
})(jQuery);
