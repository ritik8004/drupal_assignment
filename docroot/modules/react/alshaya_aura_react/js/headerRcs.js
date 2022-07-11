import displayHeader from './header';

const rcsMenu = document.getElementById('rcs-ph-navigation_menu');
let auraHeaderDisplayed = false;

(function auraHeader(Drupal) {
  Drupal.behaviors.AuraHeaderBehavior = { // eslint-disable-line no-param-reassign
    attach: function auraHeaderBehavior() {
      // If RCS menu is present, we wait until we finish loading the menu data.
      if (!auraHeaderDisplayed
        && rcsMenu
        && !rcsMenu.classList.contains('rcs-loaded')
      ) {
        return;
      }
      auraHeaderDisplayed = true;
      displayHeader();
    },
  };
}(Drupal));
