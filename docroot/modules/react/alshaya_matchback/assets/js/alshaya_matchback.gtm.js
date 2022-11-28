(function alshayaMatchback($, Drupal, drupalSettings) {
  Drupal.behaviors.alshayaMatchbackBehavior = {
    attach: function alshayaMatchbackBehavior(context) {
      $('article[data-vmode="matchback"]', context)
        .once('matchback-click-handler-attached')
        .on('click', function onMatchbackClick() {
          Drupal.alshaya_seo_gtm_push_product_clicks(
            $(this).closest('article'),
            drupalSettings.gtm.currency,
            $('body').attr('gtm-list-name').replace('PDP-placeholder', 'match back'),
          );
        });
    },
  };
}(jQuery, Drupal, drupalSettings));
