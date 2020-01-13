/**
 * @file
 * Mega menu dynamic layout.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.menuDynamicLayout = {
    attach: function (context, settings) {
      var winWidth = $(window).width();

      $('.menu--one__list-item').once().on('mouseover', function() {
        if(winWidth >= 1024){
          MegaMenuDynamicLayout($(this));
        }
      });
    }
  };

  function MegaMenuDynamicLayout ($this) {
    $this.once('MegaMenuDynamicLayout').each(function(){
      var eleL2Wrapper = $(this).children('.menu--two__list');
      var eleL2LinksWrapper = eleL2Wrapper.find('.menu__links__wrapper');
      var eleL2HighlightWrapper = eleL2Wrapper.find('.term-image__wrapper .highlights');
      var eleMainMenu = $('.megamenu-dynamic-layout');

      if (eleL2Wrapper.length > 0) {
        $(this).css('position', 'relative');
        var eleL2LinksWrapperWidth = eleL2LinksWrapper.outerWidth();
        var highlightWrapperWidth = eleL2HighlightWrapper.outerWidth();
        var l2WrapperWidth = (eleL2LinksWrapperWidth + highlightWrapperWidth + 24);
        // Assigning the width to the L2 wrapper
        eleL2Wrapper.css('width', l2WrapperWidth);

        // Get the Left position of Main Menu.
        var posEleMainMenu = eleMainMenu.offset().left;
        // Get the Left postion of L2 wrapper
        var posEleL2Wrapper = eleL2Wrapper.offset().left
        // Get the Left position of the L2 links list wrapper.
        var posL2LinksWrapper = eleL2LinksWrapper.offset().left;

        // Get the Right position of Main Menu.
        var posRightEleMainMenu = eleMainMenu.width() + posEleMainMenu;
        // Get the Right position of the L2 wrapper.
        var posRightL2Wrapper = posL2LinksWrapper + eleL2LinksWrapper.outerWidth() + eleL2HighlightWrapper.outerWidth();

        // Set the position for Arabic layout.
        if (isRTL()) {
          if (posEleL2Wrapper < posEleMainMenu) {
            var getRightPos = posEleL2Wrapper - posEleMainMenu;
            eleL2Wrapper.css('right', getRightPos);
          }
        } else {
          if (posRightEleMainMenu < posRightL2Wrapper) {
            var getLeftPos = -(posRightL2Wrapper - posRightEleMainMenu);
            eleL2Wrapper.css('left', getLeftPos);
          }
        }
      }
    });
  }

})(jQuery, Drupal);
