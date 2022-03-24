/**
 * @file
 * Mega menu dynamic layout.
 */

/* global isRTL */

(function ($, Drupal) {

  Drupal.behaviors.menuDynamicLayout = {
    attach: function (context, settings) {
      // Return if the placeholders text there in code.
      if ($('.menu__list-item:contains(#rcs.menuItem.name#)').length > 0) {
        return;
      }

      var winWidth = $(window).width();

      $('.menu--one__list-item').once().on('mouseover', function () {
        if(winWidth > 1024){
          MegaMenuDynamicLayout($(this));
        }
      });
    }
  };

  function MegaMenuDynamicLayout($this) {
    $this.once('MegaMenuDynamicLayout').each(function () {
      var eleL2Wrapper = $(this).children('.menu--two__list');
      var eleL2LinksWrapper = eleL2Wrapper.find('.menu__links__wrapper');
      var eleL2HighlightWrapper = eleL2Wrapper.find('.term-image__wrapper .highlights');
      var eleMainMenu = $(this).closest('.megamenu-dynamic-layout');

      if (eleL2Wrapper.length > 0) {
        $(this).css('position', 'relative');
        var eleL2LinksWrapperWidth = eleL2LinksWrapper.outerWidth();
        var highlightWrapperWidth = eleL2HighlightWrapper.outerWidth();
        var l2WrapperWidth = highlightWrapperWidth ? (eleL2LinksWrapperWidth + highlightWrapperWidth + 24) : eleL2LinksWrapperWidth;

        // Assigning the width to the L2 wrapper
        eleL2Wrapper.css('width', l2WrapperWidth);

        // Get the Left position of Main Menu.
        var posLeftEleMainMenu = eleMainMenu.offset().left;
        // Get the Left postion of L2 wrapper
        var posLeftEleL2Wrapper = eleL2Wrapper.offset().left;
        // Get the Left position of the L2 links list wrapper.
        var posLeftL2LinksWrapper = eleL2LinksWrapper.offset().left;

        // Get the Right position of Main Menu.
        var posRightEleMainMenu = eleMainMenu.width() + posLeftEleMainMenu;
        // Get the Right position of the L2 wrapper.
        var posRightL2Wrapper = eleL2Wrapper.outerWidth() + posLeftEleL2Wrapper;

        var croppedSectionWidth;

        // Set the position for Arabic layout.
        if (isRTL()) {
          if (posLeftEleL2Wrapper < posLeftEleMainMenu) {
            var l2BoundaryOverflowValue = posLeftEleMainMenu - posLeftEleL2Wrapper;
            var positionAdjustment = -(l2BoundaryOverflowValue);
            eleL2Wrapper.css('right', positionAdjustment);
          }
        } else {
          if (posRightEleMainMenu < posRightL2Wrapper) {
            var l2BoundaryOverflowValue = posRightL2Wrapper - posRightEleMainMenu;
            var positionAdjustment = -(l2BoundaryOverflowValue);
            eleL2Wrapper.css('left', positionAdjustment);
          }
        }

        if (isRTL()) {
          // Cropped section width calculated based on the right offset of the
          // L2 and right position by which the menu is moved. RTL has right
          // direction so need to get the window width to compare the cropped value.
          // Subtracting the calculation (offset and position) from window width
          // gives cropped section width.
          croppedSectionWidth = $(window).width() - (l2BoundaryOverflowValue + posRightL2Wrapper);
        } else {
          // Cropped section width calculated based on the left offset of the
          // L2 and left position by which the menu is moved.
          croppedSectionWidth = positionAdjustment + posLeftEleL2Wrapper;
        }

        if (croppedSectionWidth < 0) {
          eleL2Wrapper.css('overflow-x', 'auto');
          eleL2Wrapper.css(isRTL() ? 'right' : 'left', positionAdjustment + Math.abs(croppedSectionWidth));
        }

        if (eleL2Wrapper.width() > $(window).width()) {
          eleL2Wrapper.css('width', $(window).width());
        }
      }
    });
  }
})(jQuery, Drupal);
