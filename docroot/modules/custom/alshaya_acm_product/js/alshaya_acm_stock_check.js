(function ($, Drupal) {
  'use strict';

  var query_params = window.location.search;

  // Make sure query params coming from parent url are passed as is.
  if (query_params) {
    query_params = query_params.replace('?', '');
  }

  /**
   * All custom js for product page.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Js for stock check on PLP, search & PDP pages.
   */
  Drupal.behaviors.alshayaStockCheck = {
    attach: function (context, settings) {

      // Disable shareing and deliver blocks for OOS.
      $('.product-out-of-stock').once('page-load').each(function () {
        $(this).find('.sharethis-wrapper').addClass('out-of-stock');
        $(this).find('.c-accordion-delivery-options').each(function () {
          $(this).accordion('option', 'disabled', true);
        })
      });

      // Stock check on PLP, Search and Promo pages and Related products block.
      $('.stock-placeholder', context).once('check-stock').each(function () {
        var placeHolder = $(this);

        if (placeHolder.parents('.mobile-related-wrapper').length > 0) {
          return;
        }

        var articleNode = placeHolder.closest('article');
        var productId = articleNode.attr('data-nid');
        var productStock = articleNode.find('.out-of-stock');

        $.ajax({
          url: Drupal.url('stock-check-ajax/node/' + productId) + '?cacheable' + '&' + query_params,
          type: 'GET',
          dataType: 'json',
          async: true,
          success: function (result) {
            productStock.html(result.html);
          }
        });
      });

      $('.edit-add-to-cart').once('js-to-move-error-message').on('click', function () {
        if ($(this).closest('form').hasClass('ajax-submit-prevented')) {
          $('.form-item > label.error', $(this).closest('form')).each(function () {
            var parent = $(this).closest('.form-item');
            if (parent.find('.select2Option').length > 0) {
              $('.selected-text', $(parent)).append($(this));
            }
            else {
              $('label.form-required', $(parent)).append($(this));
            }
          });
        }
      });

    }
  };

})(jQuery, Drupal);
