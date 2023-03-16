(function alshayaMatchback($, Drupal, drupalSettings) {
  Drupal.behaviors.alshayaMatchbackGtmBehavior = {
    attach: function alshayaMatchbackBehavior(context) {
      $('article[data-vmode="matchback"] a.full-prod-link', context)
        .once('matchback-click-handler-attached')
        .on('click', function onMatchbackClick() {
          Drupal.alshaya_seo_gtm_push_product_clicks(
            $(this).closest('article'),
            drupalSettings.gtm.currency,
            $('body').attr('gtm-list-name').replace('PDP-placeholder', 'match backs'),
          );
        });
    },
  };

  document.addEventListener('getGtmListNameForProduct', function onGetGtmListNameForProduct(e) {
    var sku = e.detail.sku;
    var storedListValues = e.detail.storedListValues;
    if (Drupal.hasValue(storedListValues)
      && Drupal.hasValue(storedListValues[sku])
      && storedListValues[sku].indexOf('match back') > -1) {
      e.detail.listName = storedListValues[sku];
    }
    else {
      e.detail.listName = $('body').attr('gtm-list-name').replace('PDP-placeholder', 'match back');
    }
  });

  document.addEventListener('getListNameEventForRecommendation', function onGetGtmListNameForProductRecommendation(e) {
    if (e.detail.element.attr('data-vmode') !== 'matchback') {
      return;
    }
    e.detail.prefix = 'match back|'
  });
}(jQuery, Drupal, drupalSettings));
