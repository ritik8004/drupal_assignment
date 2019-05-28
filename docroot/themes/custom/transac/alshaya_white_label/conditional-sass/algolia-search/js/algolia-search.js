/**
 * @file
 * Algolia menu.
 */

/* global debounce */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.algoliaSearchMenu = {
    attach: function (context, settings) {

      $('#search_api_algolia_autocomplete_block-search').once().on('click', function (e) {
        $('.block-search-api-algolia-autocomplete-block').addClass('algolia-search-active');
      });

      // Show/hide the trending title.
      $('#search_api_algolia_autocomplete_block-search').on('keyup', function () {
        if ($('.algolia-autocomplete pre').text().length < 1) {
          $('.trending-title').show();
        }
        else {
          $('.trending-title').hide();
        }
      });

      // Add the trending title by default to algolia search suggestion list.
      $('#search_api_algolia_autocomplete_block-search').on('focus', function (e) {
        if ($('.algolia-autocomplete pre').text().length < 1 && $('.trending-title').length < 1) {
          $('.aa-dropdown-menu').prepend('<span class="trending-title">Trending searches</span>');
        }

        if ($('.algolia-autocomplete pre').text().length < 1) {
          $('.trending-title').show();
        }
        $('.block-search-api-algolia-autocomplete-block').addClass('algolia-search-active');
      });

      // Hide mobile search box, when clicked anywhere else.
      $(window).on('click touchstart', function (e) {
        if (!$(e.target).is('#search_api_algolia_autocomplete_block-search, .populate-input')) {
          if ($('.block-search-api-algolia-autocomplete-block').hasClass('algolia-search-active')) {
            $('.block-search-api-algolia-autocomplete-block').removeClass('algolia-search-active');
          }
        }
      });

      /**
       * Make Header sticky on scroll.
       */
      function stickyAlgoliaHeader() {
        var filterposition = 0;
        var supercategorymenuHeight = 0;
        var slugBannerHeight = 0;
        if ($(window).width() < 768) {
          if ($('.block-alshaya-super-category').length > 0) {
            supercategorymenuHeight = $('.block-alshaya-super-category').outerHeight() + $('.menu--mobile-navigation').outerHeight();
          }
          if ($('.region__banner-top').length > 0) {
            slugBannerHeight = $('.region__banner-top').outerHeight();
          }
          filterposition = $('.branding__menu').outerHeight() + supercategorymenuHeight + slugBannerHeight;
        }

        $(window, context).once().on('scroll', function () {
          if ($(window).width() < 768) {
            if ($(this).scrollTop() > filterposition) {
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
          $('.block-search-api-algolia-autocomplete-block').toggleClass('show-algolia-search-bar');
          $('#search_api_algolia_autocomplete_block-search').focus();
        });

        $('.algolia-search-icon').once().on('click', function (e) {
          $('.block-search-api-algolia-autocomplete-block').toggleClass('show-algolia-search-bar');
          $('.mobile--search').parent().toggleClass('search-active');
        });
      }

      // Only on listing and product pages.
      if($('.c-plp').length === 1 || $('.nodetype--acq_product').length === 1) {
        $('body').addClass('no-sticky-algolia-search-bar');
        setTimeout(function() {
          showAlgoliaSearchBar();
        }, 100);
      }
      else {
        stickyAlgoliaHeader();
      }
    }
  };

})(jQuery, Drupal);
