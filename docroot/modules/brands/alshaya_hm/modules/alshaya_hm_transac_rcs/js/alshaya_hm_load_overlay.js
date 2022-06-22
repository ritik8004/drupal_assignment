/**
 * @file
 * Load overlay attributes.
 */

(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.loadHmOverlayAttributes = {
    attach: function (context, settings) {
      $('article.node--type-rcs-product').on('click', '.pdp-overlay-details', function() {
        // Load additional attributes from mdc only once.
        $('.pdp-overlay-details').once('load-overlay').each(function () {
          let sku = $(this).closest('article').attr('data-sku');
          if (Drupal.hasValue(sku)) {
            Drupal.cartNotification.spinner_start();
            let product_attributes = window.commerceBackend.getAdditionalAttributes(sku, drupalSettings.alshayaRcs.additionalProductAttributes);
            product_attributes.then((response) => {
              // Render additional attributes in sidebar.
              let html = Drupal.renderOverlayAttributes(response);
              $('.attribute-sliderbar__content .pdp-overlay-attributes').html(html);
              Drupal.cartNotification.spinner_stop();
            });
          }
        });
      });
    }
  };

  /**
   * Return additional attributes markup.
   *
   * @param {object} response
   *   Product attributes and labels.
   */
  Drupal.renderOverlayAttributes = function (response) {
    let html = '';
    let additionalProductAttributes = drupalSettings.alshayaRcs.additionalProductAttributes;
    Object.entries(response).forEach(function ([key, value]) {
      html += '<h3>' + additionalProductAttributes[key] + '</h3>';
      html += '<ul>';
      value.forEach(function (label) {
        html += '<li>' + label + '</li>';
      });
      html += '</ul>';

    });
    return html;
  };

})(jQuery, Drupal, drupalSettings);
