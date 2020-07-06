(function ($, Drupal) {
  'use strict';
  Drupal.behaviors.rePositionSuperMenuJS = {
    attach: function () {
      setTimeout(function rePositionSuperMenu() {
        var superMenu = $('.c-menu-secondary #block-supermenu').first();

        var selectedSuperMenuParent = $(
          'body .header--wrapper'
        );

        if (superMenu && superMenu.length && selectedSuperMenuParent && selectedSuperMenuParent.length) {
          if (!($('menu--super-menu--two') && $('menu--super-menu--two').length)) {
            var superMenuClone = superMenu.clone();
            superMenuClone.attr('id', 'block-superMenuClone-two');
            superMenuClone.removeClass('menu--super-menu');
            superMenuClone.addClass('menu--super-menu--two');
            superMenuClone.prependTo(selectedSuperMenuParent);
          }
        }
      }, 0);
    }
  };
})(jQuery, Drupal);
