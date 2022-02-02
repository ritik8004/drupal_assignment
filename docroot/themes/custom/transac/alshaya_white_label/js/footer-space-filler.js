/**
 * @file
 * Footer space filler JS.
 */

(function ($, Drupal) {

  /**
   * Fill space by adding white space.
   *
   * @param {*} region
   *   jQuery object.
   * @param {integer} fillValue
   *   Value on space in pixels.
   */
  function spaceFill(region, fillValue) {
    region.addClass('auto-margin-processed');
    region.css('margin-top', fillValue + 'px');
  }

  Drupal.behaviors.footerSpaceFiller = {
    attach: function (context, settings) {
      // Blacklisted pages.
      if ($('.page-standard').hasClass('disable-footerspace-fill')) {
        return false;
      }

      $(window).on('load', function () {
        // Check if we have empty space below the footer,
        // Add that much space above it so that footer is touches the screen bottom.
        var checkoutFooter = false;
        var postContent = $('.c-post-content');
        var footerSecondary = $('.c-footer-secondary');
        var footer = $('footer');
        var footerBottom;
        var difference;
        if ($('body').hasClass('alias--checkout')
          || $('body').hasClass('alias--checkout-confirmation')
          || $('body').hasClass('alias--cart-login')) {
          checkoutFooter = true;
        }
        // Check viewport height.
        var windowHeight = $(window).height();
        // Normal Page.
        if (!checkoutFooter) {
          footerBottom = footer.position().top + footer.outerHeight();
          if (windowHeight > footerBottom) {
            difference = windowHeight - footerBottom;
            spaceFill(footer, difference);
          }
        }
        // Checkout Page.
        else {
          // On Checkout page, the footer is actually post content + footer secondary.
          // On some sites we dont have content in post content.
          var adjustRegion;

          if (postContent.length > 0) {
            footerBottom = footerSecondary.outerHeight() + postContent.position().top + postContent.outerHeight();
            adjustRegion = postContent;
          }
          else {
            footerBottom = footer.position().top + footer.outerHeight();
            adjustRegion = footer;
          }
          if (windowHeight > footerBottom) {
            difference = windowHeight - footerBottom;
            spaceFill(adjustRegion, difference);
          }
        }
      });
    }
  };

})(jQuery, Drupal);
