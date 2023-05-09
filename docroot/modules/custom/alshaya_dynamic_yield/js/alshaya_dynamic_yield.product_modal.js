/**
 * @file
 * Alshaya Social auth popup.
 */

(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.alshayaDynamicYield = {
    attach: function (context, settings) {
      // .product-quick-view-link will have to be used in the HTML for the
      // modal to open.
      $(document).once('modal-open').on('click', '.product-quick-view-link', function () {
        // No product modals to be opened for mobile.
        if ($(window).width() < 768) {
          return;
        }

        // Get recommendation title to be used in list parameter for
        // GTM events.
        var listName = $('body').attr('gtm-list-name');
        var recommendationTitle = '';
        var titleElement = $(this).closest('div[id^="dy-recommendations-"]');
        if (titleElement.length) {
          recommendationTitle += titleElement.find('.title--eng').text();
        }
        var prefix = productRecommendationsSuffix || 'pr-';
        var gtmListValues = {};
        // Try to get sku from the element clicked. Works with DY block.
        var sku = $(this).data('sku');
        if (sku) {
          // Extract only the first part prior to '|'.
          listName = listName ? listName.split('|')[0] : '';
          if (listName.indexOf('placeholder') > -1) {
            gtmListValues.list = prefix + listName.replace('placeholder', recommendationTitle).toLowerCase();
          }
          else {
            gtmListValues.list = prefix + (listName + '-' + recommendationTitle).toLowerCase();
          }
          // Override gtm list name for this product even if it already has value
          // in local storage.
          if (typeof drupalSettings.gtm !== undefined
            && typeof drupalSettings.gtm.productListExpirationMinutes !== 'undefined') {
            // Add current DY product recommendations popup in local storage with key
            // 'gtm_dy_product_list'.
            Drupal.addItemInLocalStorage('gtm_dy_product_list', gtmListValues, drupalSettings.gtm.productListExpirationMinutes);
          }
        }

        event.preventDefault();
        Drupal.ajax({
          url: Drupal.url($(this).attr('data-url-quick-view').replace('/' + drupalSettings.path.pathPrefix, '')),
          progress: { type: 'fullscreen' },
          dialogType: $(this).attr('data-dialog-type'),
          dialog: {dialogClass: 'dynamic-yield-recommendations'}
        })
        .execute();
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
