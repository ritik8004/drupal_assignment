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
        $(this).parents('.block-search-api-algolia-autocomplete-block').addClass('algolia-search-active');
        input.focus();
      });

      // On focus out make it in default state.
      if ($(window).width() > 767) {
        input.on('focusout', function (e) {
          $(this).parents('.block-search-api-algolia-autocomplete-block').removeClass('algolia-search-active');
        });
      }

      function showAlgoliaSearchresult() {
        if ($('.algolia-autocomplete pre').text().length < 1) {
          $('.trending-title').show();
          $('.algolia-autocomplete').removeClass('algolia-autocomplete-active');
        }
        else {
          $('.trending-title').hide();
          $('.algolia-autocomplete').addClass('algolia-autocomplete-active');
        }
      }

      // Show/hide the trending title.
      input.on('keyup', function () {
        showAlgoliaSearchresult();
        if (input.val().length > 0) {
          $('.algolia-form-wrapper').addClass('algolia-cleartext-active');
        }
        else {
          $('.algolia-form-wrapper').removeClass('algolia-cleartext-active');
        }

        // For first time case adding a delay.
        setTimeout(function () {
          showAlgoliaSearchresult();
        }, 50);
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
          $('.aa-dropdown-menu').prepend('<span class="trending-title">' + Drupal.t('Trending searches') + '</span>');
        }

        if ($('.algolia-autocomplete pre').text().length < 1) {
          $('.trending-title').show();
          $('.algolia-autocomplete').removeClass('algolia-autocomplete-active');
        }

        // Adding the clear text button on focus.
        if ($(this).val().length > 0) {
          $('.algolia-form-wrapper').addClass('algolia-cleartext-active');
          $('.trending-title').hide();
          $('.algolia-form-wrapper').addClass('algolia-cleartext-active');
          $('.algolia-autocomplete').addClass('algolia-autocomplete-active');
        }

        $(this).parents('.block-search-api-algolia-autocomplete-block').addClass('algolia-search-active');
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

        // On click of search icon
        $('#search_api_algolia_autocomplete_block-submit').once().on('click', function (e) {
          input.focus();
          algoliaAutocompleteBlock.addClass('algolia-search-active');
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
          $('.algolia-autocomplete').removeClass('algolia-autocomplete-active');
        });
      }

      if ($(window).width() < 768) {
        // On all the pages except front page.
        if ($('.frontpage').length < 1) {
          $('body').addClass('no-sticky-algolia-search-bar');
          setTimeout(function () {
            showAlgoliaSearchBar();
          }, 100);
        }
        else {
          // Show on non listing pages.
          $('.block-search-api-algolia-autocomplete-block').css('visibility', 'visible');
          stickyAlgoliaHeader();
        }
      }
    }
  };

})(jQuery, Drupal);
