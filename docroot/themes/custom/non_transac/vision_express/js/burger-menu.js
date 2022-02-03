/**
 * @file
 * Menu Slide.
 */

(function ($, Drupal) {

  Drupal.behaviors.slideOutMenu = {
    attach: function (context, settings) {
      var body = $('body');
      var menuButton = $('.burger');
      var closeButton = $('.menu-close');
      var menuContent = $('.menu-logo-navigation');

      function toggleMenu() {
        menuButton.toggleClass('is-hidden');
        menuContent.toggleClass('is-active');
        closeButton.toggleClass('is-active-close');
        body.toggleClass('overlay');
        menuContent.find('li, li > ul').removeClass('active');
      }

      menuButton.once().on('click', toggleMenu);
      closeButton.once().on('click', toggleMenu);

      if ($(window).width() < 1025) {
        menuContent.once().on('click', 'a, span', function (e) {
          var $this = $(this);
          if ($this.next().length > 0) {
            if ($this.closest('li').hasClass('active')) {
              menuContent.find('li, li > ul').removeClass('active');
            }
            else {
              $this.closest('li').addClass('active');
              $this.closest('li').find('ul').addClass('active');
            }
            e.preventDefault();
          }
        });
      }
    }
  };

})(jQuery, Drupal);
