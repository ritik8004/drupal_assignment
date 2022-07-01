/**
 * @file
 * Load overlay attributes.
 */

(function main ($, Drupal, drupalSettings) {

  /**
   * Return additional attributes markup.
   *
   * @param {object} response
   *   Product attributes and labels.
   *
   * @return {string}
   *   Overlay attribute markup.
   */
  var renderOverlayAttributes = function renderOverlayAttributes (response) {
    var overlayMarkup = document.createElement('div');
    var additionalProductAttributes = drupalSettings.alshayaRcs.additionalProductAttributes;
    Object.entries(response).forEach(function eachAttribute([key, value]) {
      var header = document.createElement('h3');
      header.append(additionalProductAttributes[key]);
      overlayMarkup.appendChild(header);
      var ulEle = document.createElement('ul');
      value.forEach(function (label) {
        var liEle = document.createElement('li');
        liEle.append(label);
        ulEle.appendChild(liEle)
      });
      overlayMarkup.appendChild(ulEle);
    });
    return overlayMarkup.innerHTML;
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
            var html = renderOverlayAttributes(response);
            $('.pdp-overlay-attributes').html(html);
            Drupal.cartNotification.spinner_stop();
            });
        });
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
