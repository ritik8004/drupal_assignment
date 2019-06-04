/**
 * @file
 * Algolia menu.
 */

/* global debounce */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.algoliaSearchMenu = {
    attach: function (context, settings) {

      var input = $('#search_api_algolia_autocomplete_block-search');
      var algoliaAutocompleteBlock = $('.block-search-api-algolia-autocomplete-block');

      input.once().on('click', function (e) {
        algoliaAutocompleteBlock.addClass('algolia-search-active');
        input.focus();
      });

      // On focus out make it in default state.
      if ($(window).width() > 767) {
        input.on('focusout', function () {
          algoliaAutocompleteBlock.removeClass('algolia-search-active');
        });
      }

      // Show/hide the trending title.
      input.on('keyup', function () {
        if ($('.algolia-autocomplete pre').text().length < 1) {
          $('.trending-title').show();
          $('.algolia-autocomplete').removeClass('algolia-autocomplete-active');
          $('.algolia-form-wrapper').removeClass('algolia-cleartext-active');
        }
        else {
          $('.trending-title').hide();
          $('.algolia-autocomplete').addClass('algolia-autocomplete-active');
        }

        if (input.val().length > 0) {
          $('.algolia-form-wrapper').addClass('algolia-cleartext-active');
        }
      });

      // Condition on click of clear text icon.
      $('.algolia-cleartext-icon').once().on('click', function (e) {
        input.algoliaAutocomplete('val', '');
        input.trigger('focus');
        $('.algolia-form-wrapper').removeClass('algolia-cleartext-active');
        $('.algolia-autocomplete').removeClass('algolia-autocomplete-active');
        $('.trending-title').show();
      });

      // Add the trending title by default to algolia search suggestion list.
      input.on('focus', function (e) {
        if ($('.algolia-autocomplete pre').text().length < 1 && $('.trending-title').length < 1) {
          $('.aa-dropdown-menu').prepend('<span class="trending-title">Trending searches</span>');
        }

        if ($('.algolia-autocomplete pre').text().length < 1) {
          $('.trending-title').show();
          $('.algolia-autocomplete').removeClass('algolia-autocomplete-active');
        }
        else {
          $('.algolia-autocomplete').addClass('algolia-autocomplete-active');
        }
        algoliaAutocompleteBlock.addClass('algolia-search-active');
        $('.algolia-autocomplete').removeClass('algolia-autocomplete-active');
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

        // Condition on click of back arrow.
        $('.algolia-search-icon').once().on('click', function (e) {
          input.algoliaAutocomplete('val', '');
          $('.algolia-form-wrapper').removeClass('algolia-cleartext-active');
          algoliaAutocompleteBlock.removeClass('algolia-search-active');
          $('.algolia-autocomplete').removeClass('algolia-autocomplete-active');
          $('.trending-title').show();
        });

        input.on('focusout', function (e) {
          if (input.val().length > 0) {
            $('.algolia-search-icon').once().click();
          }
          else {
            algoliaAutocompleteBlock.removeClass('algolia-search-active');
          }
        });
      }

      /**
       * Make Header sticky on scroll on SRP, PLP, product node pages.
       */
      function showAlgoliaSearchBar() {
        $('.mobile--search').once().on('click', function (e) {
          algoliaAutocompleteBlock.toggleClass('show-algolia-search-bar');
          input.focus();
          algoliaAutocompleteBlock.addClass('algolia-search-active');
        });

        $('.algolia-search-icon').once().on('click', function (e) {
          algoliaAutocompleteBlock.toggleClass('show-algolia-search-bar');
          $('.mobile--search').parent().toggleClass('search-active');
          input.algoliaAutocomplete('val', '');
          $('.trending-title').show();
          $('.algolia-form-wrapper').removeClass('algolia-cleartext-active');
          algoliaAutocompleteBlock.removeClass('algolia-search-active');
        });
      }

      if ($(window).width() < 768) {
        // Only on listing and product pages.
        if ($('.c-plp').length === 1 || $('.nodetype--acq_product').length === 1) {
          $('body').addClass('no-sticky-algolia-search-bar');
          setTimeout(function () {
            showAlgoliaSearchBar();
          }, 100);
        }
        else {
          stickyAlgoliaHeader();
        }
      }
    }
  };

})(jQuery, Drupal);
