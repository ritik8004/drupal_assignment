(function ($, Drupal) {
  Drupal.behaviors.rePositionSuperMenuJS = {
    attach: function () {
      setTimeout(function rePositionSuperMenu() {
        var superMenu = $('.c-menu-secondary #block-supermenu').first();

        var selectedSuperMenuParent = $(
          'body .header--wrapper'
        );

        if (superMenu && superMenu.length && selectedSuperMenuParent && selectedSuperMenuParent.length) {
          if (!($('menu--super-menu--clone') && $('menu--super-menu--clone').length)) {
            var superMenuClone = superMenu.clone();
            superMenuClone.attr('id', 'block-supermenuclone');
            superMenuClone.removeClass('menu--super-menu');
            superMenuClone.addClass('menu--super-menu--clone');
            superMenuClone.prependTo(selectedSuperMenuParent);
          }
        }
      }, 0);
    }
  };
})(jQuery, Drupal);
