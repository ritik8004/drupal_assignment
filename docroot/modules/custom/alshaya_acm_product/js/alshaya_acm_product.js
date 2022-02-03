/**
 * @file
 */

(function ($, Drupal) {

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
      if ($('.field--name-field-skus #product-zoom-container').length > 0) {
        $('.field--name-field-skus #product-zoom-container').each(function () {
          if ($(this).closest('td.sell-sku').length === 0) {
            if ($('.magazine-layout').length > 0 && $('.pdp-modal-overlay').length < 1) {
              $('.content__main #product-zoom-container').replaceWith($(this));
              Drupal.behaviors.magazine_gallery.attach($(this), settings);
            }
            else {
              // Execute the attach function of alshaya_product_zoom again.
              $(this).closest('.content__sidebar').siblings('.content__main').find('#product-zoom-container').replaceWith($(this));
              Drupal.behaviors.alshaya_product_zoom.attach($(this), settings);
            }
          }
          else {
            $(this).remove();
          }
        });
      }

      // For modal windows we do in attach.
      $('[data-vmode="modal"] .sku-base-form').removeClass('visually-hidden');
    }
  };

  $.fn.replaceDynamicParts = function (data) {
    if (data.replaceWith === '') {
      // Do nothing.
    }
    else {
      $(data.selector).replaceWith(data.replaceWith);

      // We trigger focus of cart button to avoid issue in iOS.
      setTimeout(function () {
        jQuery('.mobile-content-wrapper .edit-add-to-cart', $(data.selector).closest('form')).trigger('focus');
      }, 50);
    }
  };

  // Force reload on click of browser back button.
  $(window).on('popstate', function (event) {
    location.reload(true);
  });

  $.fn.updatePdpUrl = function (data) {
    // Avoid triggering url update if the SKU for which the change was triggered
    // is in a modal window.
    if ($('#drupal-modal .acq-sku-configurable-' + data.parent_sku_id + '__sku-base-form').length === 0) {
      window.history.pushState(data, data.display_node_title, data.display_node_url);
    }
  };

  /**
   * Update cross sell block, when current product form is not opened in modal.
   *
   * @param form_id
   *   The current form id.
   * @param mobile_markup
   *   The mobile cross sell markup.
   * @param desktop_markup
   *   The desktop cross sell markup.
   */
  $.fn.updateCrossSell = function (form_id, mobile_markup, desktop_markup) {
    if ($('input[value="' + form_id + '"]').parents('#drupal-modal').length == 0) {
      $('.horizontal-crossell.mobile-only-block').replaceWith(mobile_markup);
      $('.horizontal-crossell.above-mobile-block').replaceWith(desktop_markup);
    }
  };

})(jQuery, Drupal);
