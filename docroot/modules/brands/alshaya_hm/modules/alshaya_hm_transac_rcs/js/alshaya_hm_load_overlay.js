/**
 * @file
 * Load overlay attributes.
 */

(function main ($, Drupal, drupalSettings) {

  /**
   * Process overlay attributes for rendering.
   *
   * @param {object} response
   *   Product attributes and labels.
   *
   * @return {string}
   *   Overlay attribute markup.
   */
  var getOverlayAttributes = function getOverlayAttributes (response) {
    var additionalProductAttributes = drupalSettings.alshayaRcs.additionalProductAttributes;
    var data = {
      overlay_attributes: [],
    };
    Object.entries(response).forEach(function eachAttribute([key, value]) {
      data.overlay_attributes.push({
        title: additionalProductAttributes[key],
        values: value,
      });
    });
    return data;
  };

  Drupal.behaviors.loadHmOverlayAttributes = {
    attach: function attachRenderOverlay (context, settings) {
      $('article.node--type-rcs-product').once('bind-overlay-detail').on('click', '.pdp-overlay-details', function overlayClick() {
        // Load additional attributes from mdc only once.
        $('.pdp-overlay-details').once('load-overlay').each(function processOverlay() {
          var sku = $(this).closest('article').attr('data-sku');
          Drupal.cartNotification.spinner_start();
          window.commerceBackend.getAdditionalAttributes(sku, drupalSettings.alshayaRcs.additionalAttributesVariable).then(function processAttributes (response) {
            // Render additional attributes in sidebar.
            var data = getOverlayAttributes(response);
            var html = handlebarsRenderer.render('product.overlay_attributes', { data: data });
            $('.pdp-overlay-attributes').html(html);
            Drupal.cartNotification.spinner_stop();
            });
        });
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
