/**
 * @file
 * Load overlay attributes.
 */

(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.loadHmOverlayAttributes = {
    attach: function attachRenderOverlay (context, settings) {
      $('article.node--type-rcs-product').on('click', '.pdp-overlay-details', function overlayClick() {
        // Load additional attributes from mdc only once.
        $('.pdp-overlay-details').once('load-overlay').each(function processOverlay() {
          var sku = $(this).closest('article').attr('data-sku');
          Drupal.cartNotification.spinner_start();
          var productAttributes = window.commerceBackend.getAdditionalAttributes(sku, drupalSettings.alshayaRcs.additionalAttributesVariable);
          productAttributes.then(function processAttributes (response) {
            // Render additional attributes in sidebar.
            var html = renderOverlayAttributes(response);
            $('.attribute-sliderbar__content .pdp-overlay-attributes').html(html);
            Drupal.cartNotification.spinner_stop();
            });
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
  function renderOverlayAttributes (response) {
    var html = '';
    var additionalProductAttributes = drupalSettings.alshayaRcs.additionalProductAttributes;
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
