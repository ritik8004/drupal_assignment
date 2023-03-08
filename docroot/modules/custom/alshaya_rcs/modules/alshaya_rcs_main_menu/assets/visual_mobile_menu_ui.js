(function ($, Drupal) {
  Drupal.behaviors.alshayaMainMenuVisualMobileMenu = {
    attach: function (context, settings) {
      // Initiate tabs on visual mobile menu level 1.
      $('#visual-mobile-menu-level1').once('visual-mobile-tabs').tabs();

      // When we click on L1 links, all L2 should be visible and L3 hidden.
      $('.visual-mobile-menu--one__link').once('visual-mobile-l1-click').tabs().click(function () {
        $('.visual-mobile-level-two__link').show();
        $('.visual-mobile-level-three').hide();
      });

      // When we click on a L2 menu, we hide all L2 menus and show respective
      // L3 menu only.
      $('.visual-mobile-level-two__link').once('visual-mobile-l2-click').click(function (event) {
        // If L2 is last child and doesn't have any L3, open L2 link directly.
        // Else show/hide L3 items.
        if (Drupal.hasValue($(this).attr('data-children'))) {
          event.preventDefault();
          var target = $(this).attr('href');
          // Show L3 menu.
          $(target).show();
          // hide L3 menus.
          $('.visual-mobile-level-two__link').hide();
          // hide Aura block when we are in L3
          $('#aura-mobile-header-shop').hide();
        }
      });

      // When we click the back link, we show the L2 menus again.
      $('.visual-mobile-level-three .back--link a').once('visual-mobile-l3-back-link-click').click(function (event) {
        event.preventDefault();
        var target = $(this).attr('href');
        // Hide L3 menu.
        $(target).hide();
        // Show L2 menus.
        $('.visual-mobile-level-two__link').show();
        // Show Aura block when we are back in L2
        $('#aura-mobile-header-shop').show();
      });
    }
  };
})(jQuery, Drupal);
