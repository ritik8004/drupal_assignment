(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.alshayaMainMenuVisualMobileMenu = {
    attach: function (context, settings) {
      // Initiate tabs on visual mobile menu level 1.
      $('#visual-mobile-menu-level1').tabs();
    }
  };
})(jQuery, Drupal, drupalSettings);
