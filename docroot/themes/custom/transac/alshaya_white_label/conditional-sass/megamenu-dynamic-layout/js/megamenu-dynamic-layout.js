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
      var eleL2HighlightWrapper = eleL2Wrapper.find('.term-image__wrapper');
      var eleMainMenu = $('.megamenu-dynamic-layout');

      if (eleL2Wrapper.length > 0) {
        $(this).css('position', 'relative');
        // Target the first L2 list.
        var eleFirstList = eleL2Wrapper.find('.menu__links__wrapper .menu--two__list-item:eq(0)');
        // Target the last L2 list.
        var eleLastList = eleL2Wrapper.find('.menu__links__wrapper .menu--two__list-item:last-child');

        // Left position of first L2 list.
        var posFirstList = eleFirstList.offset().left;
        // Left position of last L2 list.
        var posLastList = eleLastList.offset().left;

        // Set the Width L2 wrapper for Arabic layout.
        if (isRTL()) {
          if (posLastList < posFirstList) {
            var listWidth = eleL2LinksWrapper.offset().left - posLastList;
            eleL2LinksWrapper.css('min-width', listWidth + eleL2LinksWrapper.outerWidth());
          }
        } else {
          // Checking the Left position of first and last L2.
          if (posFirstList < posLastList) {
            var listWidth = posLastList - eleL2LinksWrapper.offset().left ;
            eleL2LinksWrapper.css('min-width', listWidth + eleLastList.outerWidth());
          }
        }

        var eleL2LinksWrapperWidth = eleL2LinksWrapper.outerWidth();
        var highlightWrapperWidth = eleL2HighlightWrapper.outerWidth();
        var l2WrapperWidth = (eleL2LinksWrapperWidth + highlightWrapperWidth);

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
