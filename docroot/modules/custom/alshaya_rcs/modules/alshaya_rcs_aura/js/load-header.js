(function loadAuraHeader(Drupal, $) {
  const $rcsMenu = $('#rcs-ph-navigation_menu');
  let auraHeaderDisplayed = false;

  // eslint-disable-next-line
  Drupal.behaviors.AuraHeaderBehavior = {
    attach: function auraHeaderBehavior() {
      // If RCS menu is present, we wait until we finish loading the menu data.
      if (!auraHeaderDisplayed
        && $rcsMenu.length
        && !$rcsMenu.hasClass('rcs-loaded')
      ) {
        return;
      }
      auraHeaderDisplayed = true;
      Drupal.displayAuraHeader();
    },
  };
}(Drupal, jQuery));
