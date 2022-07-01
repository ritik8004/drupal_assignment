/**
 * @file
 * Load overlay attributes.
 */

(function main ($, Drupal, drupalSettings) {

  Drupal.behaviors.loadHmOverlayAttributes = {
    attach: function attachRenderOverlay (context, settings) {
      $('article.node--type-rcs-product').on('click', '.pdp-overlay-details', function overlayClick() {
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
      var header = document.createElement('h3');
      header.append(additionalProductAttributes[key]);
      html += header.outerHTML;
      var ulEle = document.createElement('ul');
      value.forEach(function (label) {
        var liEle = document.createElement('li');
        liEle.append(label);
        ulEle.appendChild(liEle)
      });
      html += ulEle.outerHTML;
    });
    return html;
  };

})(jQuery, Drupal, drupalSettings);
