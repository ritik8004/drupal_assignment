(function ($, Drupal) {
  'use strict';

  /**
   * All custom js for product page.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   All custom js for product page.
   */
  Drupal.behaviors.alshaya_acm_product = {
    attach: function (context, settings) {
      // If we find the gallery in add cart form ajax response, we update the main gallery.
      if ($('.field--name-field-skus #product-zoom-container').size() > 0) {
        $('.field--name-field-skus #product-zoom-container').each(function() {
          if ($(this).closest('td.sell-sku').length === 0) {
            // Execute the attach function of alshaya_product_zoom again.
            Drupal.behaviors.alshaya_product_zoom.attach($(this), settings);
            $(this).closest('.content__sidebar').siblings('.content__main').find('#product-zoom-container').replaceWith($(this));
          }
          else {
            $(this).remove();
          }
        });
      }
    }
  };

  /**
   * Toggle delivery options' visibility based on stock check.
   */
  Drupal.AjaxCommands.prototype.toggleDeliveryOptions = function (ajax, response, status) {
    var output = response.data.alshaya_acm;
    var $article = $('article[data-vmode="full"][data-nid="'+ output.nid +'"]');
    if (output.delivery_opt) {
      $article.find('.delivery-options-wrapper > h2.field__label').removeClass('disabled');
      $article.find('.click-collect').accordion('option', {disabled: false});
      $article.find('.home-delivery').accordion('option', {disabled: false});
    }
    else {
      $article.find('.delivery-options-wrapper > h2.field__label').addClass('disabled');
      $article.find('.click-collect').accordion('option', {active: false, disabled: true});
      $article.find('.home-delivery').accordion('option', {active: false, disabled: true});
    }
  };

})(jQuery, Drupal);
