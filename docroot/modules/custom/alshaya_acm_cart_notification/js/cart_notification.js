(function ($) {
  Drupal.behaviors.alshaya_acm_cart_notification = {
    attach: function (context, settings) {
      var element = document.getElementById('cart_notification');
      var dialogsettings = {
        autoOpen: true,
        dialogClass: 'dialog-cart-notification',
        title: '',
        close: function() {
          $("#sku-base-form").append('<div id = "cart_notification"></div>');
        }
      };
      var myDialog = Drupal.dialog(element, dialogsettings);
      // Avoiding empty dialog during first page load.
      if($(element).html().length != 0) {
        myDialog.show();
        myDialog.showModal();
       }
    }
  };
})(jQuery);
