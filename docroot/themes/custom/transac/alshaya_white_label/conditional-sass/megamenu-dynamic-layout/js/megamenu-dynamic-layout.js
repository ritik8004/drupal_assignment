/**
 * @file
 * Mega menu dynamic layout.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.menuDynamicLayout = {
    attach: function (context, settings) {
      var winWidth = $(window).width();

      function MegaMenuDynamicLayout ($this) {
        $this.once('setListWidth').each(function(){
          var eleL2Wrapper = $(this).children('.menu--two__list');
          var eleL2WrapperLinks = eleL2Wrapper.find('.menu__links__wrapper');
          var eleMainMenu = $('.menu--one__list');

          if (eleL2Wrapper.length > 0) {
            $(this).css('position', 'relative');
            // Target the first L2.
            var eleFirstList = eleL2Wrapper.find('.menu__links__wrapper .menu--two__list-item:eq(0)');
            // Target the last L2.
            var eleLastList = eleL2Wrapper.find('.menu__links__wrapper .menu--two__list-item:last-child');

            // Left position of first L2.
            var posFirstList = eleFirstList.offset().left;
            // Left position of last L2.
            var posLastList = eleLastList.offset().left;

            // Set the Width L2 wrapper for English layout.
            if ($('html').attr('dir') === 'ltr') {
              // Checking the Left position of first and last L2.
              if (posFirstList < posLastList) {
                var listWidth = posLastList - eleL2WrapperLinks.offset().left ;
                eleL2WrapperLinks.css('min-width', listWidth + eleLastList.outerWidth());
              }
              // Set the Width L2 wrapper for Arabic layout.
            } else if ($('html').attr('dir') === 'rtl') {
              if (posLastList < posFirstList) {
                var listWidth = eleL2WrapperLinks.offset().left - posLastList;
                eleL2WrapperLinks.css('min-width', listWidth + eleL2WrapperLinks.outerWidth());
              }
            }

            var eleL2WrapperLinksWidth = eleL2WrapperLinks.outerWidth();
            var highlightWrapperWidth = eleL2Wrapper.find('.term-image__wrapper').outerWidth();
            var l2WrapperWidth = (eleL2WrapperLinksWidth + highlightWrapperWidth);

            // Assigning the width to the L2 wrapper
            eleL2Wrapper.css('width', l2WrapperWidth);

            // Get the Right position of Main Menu.
            var posEleMainMenu = eleMainMenu.width() + eleMainMenu.offset().left;
            // Get the Left position of the L2 wrapper.
            var posL2WrapperLinks = eleL2WrapperLinks.offset().left;
            // Get the Right position of the L2 wrapper.
            var posRightL2WrapperLinks = posL2WrapperLinks + eleL2WrapperLinks.outerWidth() + eleL2Wrapper.find('.term-image__wrapper').outerWidth();

            // Set the position for English layout.
            if ($('html').attr('dir') === 'ltr') {
              if (posEleMainMenu < posRightL2WrapperLinks) {
                var MinusLeftPos = (posRightL2WrapperLinks - posEleMainMenu);
                eleL2Wrapper.css('left', -MinusLeftPos);
              }
              // Set the position for Arabic layout.
            } else if ($('html').attr('dir') === 'rtl') {
              if (posL2WrapperLinks < eleMainMenu.offset().left) {
                var MinusLeftPos = posL2WrapperLinks - eleMainMenu.offset().left;
                eleL2Wrapper.css('right', MinusLeftPos);
              }
            }
          }
        });
      }

      $('.menu--one__list-item').once().on('mouseover', function() {
        if(winWidth >= 1024){
          MegaMenuDynamicLayout($(this));
        }
      });
    }
  };

})(jQuery, Drupal);
