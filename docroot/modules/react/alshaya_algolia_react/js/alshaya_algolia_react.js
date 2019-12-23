/**
 * @file
 * All Filters Panel & Facets JS file.
 */
(function ($, Drupal) {
  'use strict';

  // Plug into XHR requests to identify and trigger events when Algolia
  // finishes loading search results.
  var origOpen = XMLHttpRequest.prototype.open;
  XMLHttpRequest.prototype.open = function (method, url) {
    this.addEventListener('load', function () {
      if (url.indexOf('algolia') > -1 && url.indexOf('instantsearch') > -1) {
        $('#alshaya-algolia-search').trigger('search-results-updated');
      }
    });
    origOpen.apply(this, arguments);
  };

  Drupal.behaviors.alshayaAlgoliaReact = {
    attach: function (context, settings) {
      $(window).on('blazySuccess', function(event, element) {
        Drupal.plpListingProductTileHeight('row', element);
      });

      // Close the facets on click anywherer outside.
      $(window).on('click', function(event) {
        var facet_block = $('.container-without-product .c-collapse-item');
        if ($(facet_block).find(event.target).length == 0) {
          $(facet_block).find('.c-facet__title').removeClass('active');
          $(facet_block).find('ul').slideUp();
        }
      });
      $(window).on('load', function(event) {
        $('body').once('bind-facet-item-click').on('click','.c-collapse-item .facet-item', function(event) {
          $(this).parents('.c-facet.c-collapse-item').find('.c-facet__title.c-collapse__title.active').trigger('click');
        });
      });

      if ($('#alshaya-algolia-search').length > 0) {
        Drupal.algoliaReact.facetEffects();
      }
    }
  };

  Drupal.refreshGrids = function() {
    Drupal.plpListingProductTileHeight('full_page', null);
  };

  /**
   * Calculate and add height for each product tile.
   */
  Drupal.listingProductTileHeight = function () {
    if ($(window).width() > 1024) {
      var maxHeight = 0;
      $('.c-products__item').each(function () {
        var currentHeight = $(this)
          .find('> article')
          .outerHeight(true);
        maxHeight = maxHeight > currentHeight ? maxHeight : currentHeight;
      });

      $('.c-products__item').css('height', maxHeight);
    }
  };

  Drupal.algoliaReact = Drupal.algoliaReact || {};

  // Show all filters blocks.
  Drupal.algoliaReact.facetEffects = function () {
    // On clicking facet block title, update the title of block and hide
    // other facets.
    $('.all-filters-algolia .c-collapse-item').once('algolia-search').on('click', function() {
      var all_filters = $(this).parents('.all-filters-algolia');
      // Update the title on click of facet.
      var facet_title = $(this).find('h3.c-facet__title').html();
      $('.filter-sort-title', all_filters).html(facet_title);

      // Only show current facet and hide all others.
      $(this).removeClass('show-facet');
      $('.all-filters-algolia .c-collapse-item').hide();
      $(this).addClass('show-facet');

      // Show the back button.
      $('.back-facet-list', all_filters).show();
      // Update the the hidden field with the id of selected facet.
      all_filters.parent().find('#all-filter-active-facet-sort').val($(this).attr('id'));
    });

    // On clicking on back button, reset the block title and add class so
    // that facet blocks can be closed.
    $('.all-filters-algolia .back-facet-list').once('algolia-search').on('click', function() {
      $('.c-collapse-item', all_filters).find('ul').hide();
      var all_filters = $(this).parents('.all-filters-algolia');
      $(this).hide();
      $('.filter-sort-title', all_filters).html(Drupal.t('filter & sort'));
      $('.c-collapse-item', all_filters).removeClass('show-facet');
      $('.c-collapse-item', all_filters).not('.hide-facet-block').show();
      $('.c-collapse-item .c-facet__title', all_filters).removeClass('active');
      // Reset the hidden field value.
      all_filters.parent().find('#all-filter-active-facet-sort').val('');
    });

    // Grid switch for PLP and Search pages.
    $('#alshaya-algolia-search .small-col-grid').once('algolia-search').on('click', function () {
      var algolia_wrapper = $(this).parents('#alshaya-algolia-search');
      $('.large-col-grid', algolia_wrapper).removeClass('active');
      $(this).addClass('active');
      $('body').removeClass('large-grid')
      $('.c-products-list', algolia_wrapper).removeClass('product-large').addClass('product-small');
      // Adjust height of PLP tiles.
      Drupal.plpListingProductTileHeight('full_page', null);
    });

    $('#alshaya-algolia-search .large-col-grid').once('algolia-search').on('click', function () {
      var algolia_wrapper = $(this).parents('#alshaya-algolia-search');
      $('.small-col-grid', algolia_wrapper).removeClass('active');
      $(this).addClass('active');
      $('body').addClass('large-grid')
      $('.c-products-list', algolia_wrapper).removeClass('product-small').addClass('product-large');
      // Adjust height of PLP tiles.
      Drupal.plpListingProductTileHeight('full_page', null);
    });


    // Add dropdown effect for facets filters.
    $('.c-facet__title.c-collapse__title').once('algolia-search').on('click', function () {
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

    $('.sticky-filter-wrapper .show-all-filters-algolia').once('algolia-search').on('click', function() {
      $('.all-filters-algolia').addClass('filters-active');

      if ($(window).width() > 1023) {
        $('html').addClass('all-filters-overlay');
      }
      else {
        $('body').addClass('mobile--overlay');
      }

      $('.all-filters-algolia .c-collapse-item').removeClass('show-facet');

      var active_filter_sort = $('#all-filter-active-facet-sort').val();
      // On clicking `all` filters, check if there was filter which selected last.
      if (active_filter_sort.length > 0) {
        $('.all-filters-algolia #' + active_filter_sort).show();
        $('.all-filters-algolia #' + active_filter_sort).addClass('show-facet');
      }
      else {
        $('.all-filters-algolia .c-collapse__title.active').parent('.c-collapse-item').addClass('show-facet');
      }

      $('.all-filters-algolia').show();
    });

    // Fake facet apply button to close the `all filter`.
    $('.all-filters-algolia .all-filters-close, .all-filters-algolia .facet-apply-all').once('algolia-search').on('click', function() {
      $('.all-filters-algolia').removeClass('filters-active');
      $('body').removeClass('mobile--overlay');
      $('html').removeClass('all-filters-overlay');
    });
  };

  /**
   * Make Header sticky on scroll.
   */
  Drupal.algoliaReact.stickyfacetfilter = function () {
      var algoliaReactFilterPosition = 0;
      var superCategoryMenuHeight = 0;
      var position = 0;
      var filter = $('#alshaya-algolia-search .region__content');
      var nav = $('.branding__menu');
      var fixedNavHeight = 0;

      if ($('.show-all-filters-algolia').length > 0) {
        if ($(window).width() > 1023) {
          algoliaReactFilterPosition = $('#alshaya-algolia-search .container-without-product').offset().top;
        } else if ($(window).width() > 767 && $(window).width() < 1024) {
          algoliaReactFilterPosition = $('#alshaya-algolia-search .show-all-filters-algolia').offset().top;
        } else {
          if ($('.block-alshaya-super-category').length > 0) {
            superCategoryMenuHeight = $('.block-alshaya-super-category').outerHeight() + $('.menu--mobile-navigation').outerHeight();
          }
          if ($('#alshaya-algolia-search .show-all-filters-algolia').length > 0) {
            algoliaReactFilterPosition = $('#alshaya-algolia-search .show-all-filters-algolia').offset().top - $('.branding__menu').outerHeight() - superCategoryMenuHeight;
          }
          fixedNavHeight = nav.outerHeight() + superCategoryMenuHeight;
        }
      }

    if ($('.show-algolia-result').length > 0) {
      $(window).once('algoliaStickyFilter').on('scroll', function () {
        // Sticky filter header.
        if ($('#alshaya-algolia-search .show-all-filters-algolia').length > 0 && $('.show-algolia-result').length > 0) {
          if ($(this).scrollTop() > algoliaReactFilterPosition) {
            filter.addClass('filter-fixed-top');
            $('body').addClass('header-sticky-filter');
          } else {
            filter.removeClass('filter-fixed-top');
            $('body').removeClass('header-sticky-filter');
          }
        }
      });
    }
  };

  /**
   * Wrapping all the filters inside a div to make it sticky.
   */
  function stickyfacetwrapper() {
    if ($('.show-all-filters-algolia').length > 0) {
      if ($(window).width() > 767) {
          var site_brand = $('.site-brand-home').clone();
          $(site_brand).insertBefore('#alshaya-algolia-search .container-without-product');
      }
      else {
        if ($('.region__content > .all-filters-algolia').length < 1) {
          $('.all-filters-algolia').insertAfter('#block-page-title');
        }
      }
    }
  }

})(jQuery, Drupal);
