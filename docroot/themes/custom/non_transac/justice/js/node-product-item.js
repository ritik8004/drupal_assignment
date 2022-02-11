/**
 * @file
 * plp share product.
 */

(function ($, Drupal) {

  Drupal.behaviors.shareProduct = {
    attach: function (context, settings) {
      var bodyEle = $('body');
      var shareIconEle = $('div.view-product-item__share--label');
      var productEle = $('.view-product-item__inner-container');
      var overlayClass = 'share-overlay';

      bodyEle.on('click', function (event) {
        if ($(event.target).is(shareIconEle)) {
          var local = $(event.target).closest(productEle);
          if (local.hasClass(overlayClass)) {
            local.removeClass(overlayClass);
          }
          else {
            bodyEle.find(productEle).removeClass(overlayClass);
            local.addClass(overlayClass);
          }
        }
        else {
          if (!$(event.target).hasClass(overlayClass)) {
            bodyEle.find(productEle).removeClass(overlayClass);
          }
        }
      });
    }
  };
})(jQuery, Drupal);
