/**
 * @file
 * All Filters Panel & Facets JS file.
 */
(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.alshayaAlgoliaReact = {
    attach: function (context, settings) {

      // Grid switch for PLP and Search pages.
      $('.small-col-grid').once().on('click', function () {
        $('.large-col-grid').removeClass('active');
        $(this).addClass('active');
        $('body').removeClass('large-grid')
        $('.c-products-list').removeClass('product-large').addClass('product-small');

         // Adjust height of PLP tiles.
         Drupal.listingProductTileHeight();
      });
      $('.large-col-grid').once().on('click', function () {
        $('.small-col-grid').removeClass('active');
        $(this).addClass('active');
        $('body').addClass('large-grid')
        $('.c-products-list').removeClass('product-small').addClass('product-large');
         // Adjust height of PLP tiles.
         Drupal.listingProductTileHeight();
      });


      // Add dropdown effect for facets filters.
      $('.c-facet__title.c-accordion__title').once().on('click', function () {
        if ($(this).hasClass('active')) {
          $(this).removeClass('active');
          // We want to run this only on main page facets.
          if (!$(this).parent().parent().hasClass('filter__inner')) {
            $(this).siblings('ul').slideUp();
          }
        }
        else {
          if (!$(this).parent().parent().hasClass('filter__inner')) {
            $(this).parent().siblings('.c-facet').find('.c-facet__title.active').siblings('ul').slideUp();
          }
          $(this).parent().siblings('.c-facet').find('.c-facet__title.active').removeClass('active');

          $(this).addClass('active');
          if (!$(this).parent().parent().hasClass('filter__inner')) {
            $(this).siblings('ul').slideDown();
          }
        }
      });

      // Close the facets on click anywherer outside.
      document.addEventListener('click', function(event) {
        var facet_block = $('.c-content .region__content .container-without-product .c-accordion');
        if ($(facet_block).find(event.target).length == 0) {
          $(facet_block).find('.c-facet__title').removeClass('active');
          $(facet_block).find('ul').slideUp();
        }
      });

      /**
       * Make Header sticky on scroll.
       */
      function stickyfacetfilter() {
        var filterposition = 0;
        var supercategorymenuHeight = 0;
        var position = 0;
        var filter = $('.region__content');
        var nav = $('.branding__menu');
        var fixedNavHeight = 0;

        if ($('.show-all-filters').length > 0) {
          if ($(window).width() > 1023) {
            filterposition = $('.container-without-product').offset().top;
          }
          else if ($(window).width() > 767 && $(window).width() < 1024) {
            filterposition = $('.show-all-filters').offset().top;
          }
          else {
            if ($('.block-alshaya-super-category').length > 0) {
              supercategorymenuHeight = $('.block-alshaya-super-category').outerHeight() + $('.menu--mobile-navigation').outerHeight();
            }
            filterposition = $('.show-all-filters').offset().top - $('.branding__menu').outerHeight() - supercategorymenuHeight;
            fixedNavHeight = nav.outerHeight() + supercategorymenuHeight;
          }
        }

        $(window, context).once().on('scroll', function () {
          // Sticky filter header.
          if ($('.show-all-filters').length > 0) {
            if ($(this).scrollTop() > filterposition) {
              filter.addClass('filter-fixed-top');
              $('body').addClass('header-sticky-filter');
            }
            else {
              filter.removeClass('filter-fixed-top');
              $('body').removeClass('header-sticky-filter');
            }
          }
        });
      };
      stickyfacetfilter();

      /**
       * Wrapping all the filters inside a div to make it sticky.
       */
      function stickyfacetwrapper() {
        if ($('.show-all-filters').length > 0) {
          if ($(window).width() > 767) {
              var site_brand = $('.site-brand-home').clone();
              $(site_brand).insertBefore('#alshaya-algolia-search .container-without-product');
          }
          else {
            if ($('.region__content > .all-filters').length < 1) {
              $('.all-filters').insertAfter('#block-page-title');
            }
          }
        }
      }
      stickyfacetwrapper();
    }
  }

  /**
   * Calculate and add height for each product tile.
   */
  Drupal.listingProductTileHeight = function () {
    if ($(window).width() > 1024) {
      var Hgt = 0;
      $('.c-products__item').each(function () {
        var Height = $(this)
          .find('> article')
          .outerHeight(true);
        Hgt = Hgt > Height ? Hgt : Height;
      });

      $('.c-products__item').css('height', Hgt);
    }
  };

})(jQuery, Drupal);
