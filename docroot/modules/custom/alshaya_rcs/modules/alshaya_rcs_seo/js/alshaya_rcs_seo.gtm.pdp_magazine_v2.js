(function alshayaRcsSeoGtm(Drupal, $) {
  var productDetailsViewTriggered = false;
  var $pdpRootElement = $('#pdp-layout');

  Drupal.behaviors.alshayaRcsSeoGtmBehavior = {
    attach: function alshayaRcsSeoGtm() {
      if (!productDetailsViewTriggered && $pdpRootElement.children().length) {
        productDetailsViewTriggered = true;
        // Trigger productDetailView event.
        Drupal.alshayaSeoGtmPushProductDetailView($pdpRootElement);
      }
    }
  }
})(Drupal, jQuery);
