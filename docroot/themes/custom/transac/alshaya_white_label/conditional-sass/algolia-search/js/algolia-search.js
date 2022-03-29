/**
 * @file
 * Algolia Search Block.
 */

/* global debounce */

(function ($, Drupal) {

  /* The solution has been encouraged from https://css-tricks.com/the-trick-to-viewport-units-on-mobile
  *
  * For some devices the css viewport height is not calculated correctly and does not match the window height
  * and hence the layout breaks, especially for iOS devices.
  * This JS approach passes the correct viewport height value to css through css variable
  *
  */
  if ($(window).width() < 768) {
    // First we get the viewport height and we multiple it by 1% to get a value for a vh unit
    var vh = window.innerHeight * 0.01;
    // Then we set the value in the --vh custom property to the root of the document
    document.documentElement.style.setProperty('--vh', "".concat(vh, "px"));
  }

  Drupal.behaviors.algoliaSearchMenu = {
    attach: function (context, settings) {
      var algoliaAutocompleteBlock = $('.block-alshaya-algolia-react-autocomplete');

      /**
       * Make Algolia Search Block Sticky on scroll.
       */
      function stickyAlgoliaHeader() {
        var filterPosition = 0;
        var superCategoryMenuHeight = 0;
        var slugBannerHeight = 0;
        if ($(window).width() < 768) {
          if ($('.block-alshaya-super-category').length > 0) {
            superCategoryMenuHeight = $('.block-alshaya-super-category').outerHeight() + $('.menu--mobile-navigation').outerHeight();
          }
          if ($('.region__banner-top').length > 0) {
            slugBannerHeight = $('.region__banner-top').outerHeight();
          }
          filterPosition = $('.branding__menu').outerHeight() + superCategoryMenuHeight + slugBannerHeight;
        }

        $(window).once('algoliaReactSearch').on('scroll', function () {
          if ($(window).width() < 768) {
            if ($(this).scrollTop() > filterPosition) {
              $('body').addClass('Sticky-algolia-search');
            }
            else {
              $('body').removeClass('Sticky-algolia-search');
            }
          }
        });
      }

      /**
       * Make Header sticky on scroll on SRP, PLP, product node pages.
       */
      function showAlgoliaSearchBar() {
        $('.mobile--search').once().on('click', function (e) {
          $('body').toggleClass('search-enabled');
          algoliaAutocompleteBlock.toggleClass('show-algolia-search-bar');
          algoliaAutocompleteBlock.find('input').trigger('focus');
        });
        $('.algolia-search-back-icon').once().on('click', function (e) {
          $('body').removeClass('search-enabled');
        });
      }

      if ($(window).width() <= 1024) {
        // On all the pages except front page.
        if ($('.frontpage').length < 1) {
          $('body').addClass('no-sticky-algolia-search-bar');
          setTimeout(function () {
            showAlgoliaSearchBar();
          }, 100);
        }
        else {
          // On Front page.
          $('.block-alshaya-algolia-react-autocomplete').css('visibility', 'visible');
          stickyAlgoliaHeader();
        }
      }
    }
  };

})(jQuery, Drupal);
