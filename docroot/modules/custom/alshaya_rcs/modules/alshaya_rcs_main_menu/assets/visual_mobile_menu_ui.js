(function ($, Drupal) {
  Drupal.behaviors.alshayaMainMenuVisualMobileMenu = {
    attach: function (context, settings) {
      // Initiate tabs on visual mobile menu level 1.
      $('#visual-mobile-menu-level1').once('visual-mobile-tabs').tabs();

      // When we click on a L2 menu, we hide all L2 menus and show respective
      // L3 menu only.
      $('.visual-mobile-level-two__link').once('visual-mobile-l2-click').click(function () {
        var target = $(this).attr('href');
        // Show L3 menu.
        $(target).show();
        // hide L3 menus.
        $('.visual-mobile-level-two__link').hide();
      });

      // When we click the back link, we show the L2 menus again.
      $('.visual-mobile-level-three .back--link a').once('visual-mobile-l3-back-link-click').click(function () {
        var target = $(this).attr('href');
        // Hide L3 menu.
        $(target).hide();
        // Show L2 menus.
        $('.visual-mobile-level-two__link').show();
      });
    }
  };
})(jQuery, Drupal);
