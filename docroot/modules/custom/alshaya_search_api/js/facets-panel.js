/**
 * @file
 * PLP - All Filters Panel & Facets JS file.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.facetPanel = {
    attach: function (context, settings) {

      // Grid switch for PLP and Search pages.
      $('.small-col-grid').once().on('click', function () {
        $('.large-col-grid').removeClass('active');
        $(this).addClass('active');
        $('body').removeClass('large-grid');
        $('.c-products-list').removeClass('product-large').addClass('product-small');
        setTimeout(function() {
          $('.search-lightSlider').slick('refresh');
         }, 300);
         // Adjust height of PLP tiles.
         Drupal.plpListingProductTileHeight('full_page', null);
      });
      $('.large-col-grid').once().on('click', function () {
        $('.small-col-grid').removeClass('active');
        $(this).addClass('active');
        $('body').addClass('large-grid');
        $('.c-products-list').removeClass('product-small').addClass('product-large');
        setTimeout(function() {
          $('.search-lightSlider').slick('refresh');
         }, 300);
         // Adjust height of PLP tiles.
         Drupal.plpListingProductTileHeight('full_page', null);
      });

      // On filter selection keeping the selected layout.
      if ($('body').hasClass('large-grid')) {
        $('.large-col-grid').click();
      }

      // On clicking facet block title, update the title of block and hide
      // other facets.
      $('.all-filters .block-facets-ajax').on('click', function() {
        // Update the title on click of facet.
        var facet_title = $(this).find('h3.c-facet__title').html();
        $('.filter-sort-title').html(facet_title);

        // Only show current facet and hide all others.
        $(this).removeClass('show-facet');
        $('.all-filters .block-facets-ajax').hide();
        $('.all-filters .bef-exposed-form').hide();
        $(this).addClass('show-facet');

        // Show the back button.
        $('.facet-all-back').show();
        // Update the the hidden field with the id of selected facet.
        $('#all-filter-active-facet-sort').val($(this).attr('id'));
      });

      // On clicking of sort option, update title
      $('.all-filters .bef-exposed-form').on('click', function() {
        // Update the title on click of facet.
        var facet_title = $(this).find('span.fieldset-legend').html();
        $('.filter-sort-title').html(facet_title);

        // Only show current facet and hide all others.
        $(this).removeClass('show-facet');
        $('.all-filters .block-facets-ajax').hide();
        $(this).addClass('show-facet');
        $(this).find('legend').addClass('active');

        // Show the back button.
        $('.facet-all-back').show();
        // Update the the hidden field with the id of sort block.
        $('#all-filter-active-facet-sort').val($(this).attr('id'));
      });

      // On clicking on back button, reset the block title and add class so
      // that facet blocks can be closed.
      $('.facet-all-back').on('click', function() {
        $(this).hide();
        $('.filter-sort-title').html(Drupal.t('filter & sort'));
        $('.all-filters .bef-exposed-form, .all-filters .block-facets-ajax').removeClass('show-facet');
        $('.all-filters .bef-exposed-form, .all-filters .block-facets-ajax').show();
        $('.all-filters .bef-exposed-form legend').removeClass('active');
        $('.all-filters .block-facets-ajax .c-facet__title').removeClass('active');
        // Reset the hidden field value.
        $('#all-filter-active-facet-sort').val('');
      });

      // Show all filters blocks.
      $('.show-all-filters').once().on('click', function() {
        $('.all-filters').addClass('filters-active');

        if ($(window).width() > 1023) {
          $('html').addClass('all-filters-overlay');
        }
        else {
          $('body').addClass('mobile--overlay');
        }

        $('.all-filters .bef-exposed-form, .all-filters .block-facets-ajax').removeClass('show-facet');

        var active_filter_sort = $('#all-filter-active-facet-sort').val();
        // On clicking `all` filters, check if there was filter which selected last.
        if (active_filter_sort.length > 0) {
          $('.all-filters #' + active_filter_sort).show();
          $('.all-filters #' + active_filter_sort).addClass('show-facet');
        }
        $('.all-filters').show();
      });

      // Fake facet apply button to close the `all filter`.
      $('.facet-all-apply', context).once().on('click', function() {
        $('.all-filters').removeClass('filters-active');
        $('body').removeClass('mobile--overlay');
        $('html').removeClass('all-filters-overlay');
        // Show filter count if applicable.
        showFilterCount();
      });

      if ($('.c-content__region .region__content  > div.block-facets-summary li.clear-all').length > 0) {
        var clearAll = $('.c-content__region .region__content  > div.block-facets-summary').clone();
        // Remove all `li` except last.
        $(clearAll).find('li:not(:last)').remove();
        $('.facet-all-clear').html(clearAll);
        $('.facet-all-clear').addClass('has-link');
      }
      else {
        $('.facet-all-clear').html(Drupal.t('clear all'));
        $('.facet-all-clear').removeClass('has-link');
      }

      // On change of outer `sort by`, update the 'all filter' sort by as well.
      $('.c-content .c-content__region .bef-exposed-form input:radio').on('click', function() {
        var idd = $(this).attr('id');
        $('.c-content__region .bef-exposed-form input:radio').attr('checked', false);
        $('.c-content__region .bef-exposed-form #' + idd).attr('checked', true);
        updateSortTitle();
      });

      // Sort result on change of sort in `All filters`.
      $('.all-filters .bef-exposed-form input:radio').on('click', function (e) {
        // Get ID.
        var idd = $(this).attr('id');
        $('.c-content .c-content__region .bef-exposed-form #' + idd).attr('checked', true);
        // Trigger click of button.
        var idd = $('.c-content .c-content__region [data-bef-auto-submit-click]').first().attr('id');
        $('#' + idd).trigger('click');
        updateSortTitle();
        // Stopping other propagation.
        e.stopPropagation();
      })

      stickyfacetwrapper();
      showOnlyFewFacets();
      updateSortTitle();
      updateFacetTitlesWithSelected();
      updateCategoryTitle();

      $(window).on('blazySuccess', function(event, element) {
        Drupal.plpListingProductTileHeight('row', element);
      });

      // Back to PLP and loading a PLP/SRP with facets active in URL.
      if (context === $(document)[0]) {
        showFilterCount();
      }

      /**
       * Show filtercount on mobile on toggle buttons.
       */
      function showFilterCount() {
        // Only for mobile.
        if ($(window).width() < 768) {
          var filterBarSelector;
          if ($('body').hasClass('plp-page-only')) {
            filterBarSelector = '.block-facets-summary-blockfilter-bar-plp';
          }
          else if ($('body').hasClass('nodetype--acq_promotion')) {
            filterBarSelector = '.block-facets-summary-blockfilter-bar-promotions';
          }
          else {
            filterBarSelector = '.block-facets-summary-blockfilter-bar';
          }

          if ($(filterBarSelector +' ul .applied-filter').length < 1) {
            $(filterBarSelector +' ul li:not(.clear-all)').wrapAll('<div class="applied-filter"></div>');
          }
          var height = $(filterBarSelector+ ' .applied-filter').height();
          // Add a max-height if there are filters on third line.
          if (height > 82) {
            $(filterBarSelector + ' .applied-filter').addClass('max-height');
          }

          // Count the number of filters on the third line onwards.
          var count = 0;
          $(filterBarSelector+ ' ul .applied-filter li').each(function () {
            if ($(this).position().top > 41) {
              count ++;
            }
          });
          if (count > 0) {
            $(filterBarSelector + ' .filter-toggle-mobile').show();
            $(filterBarSelector + ' .filter-toggle-mobile').html(count);
            $('.filter-toggle-mobile', context).once().on('click', function () {
              $(this).toggleClass('active');
              $(filterBarSelector + ' .applied-filter').toggleClass('max-height');
            });
          }
        }
      }

      /**
       * Show only 4 facets by default and hide others.
       */
      function showOnlyFewFacets() {
        var facets = $('.c-content__region .region__content .container-without-product > div.block-facets-ajax:not(:empty)');
        if (facets.length > 0) {
          var total_facets = facets.length;
          // By default only show 4 facets.
          var show_only_facets, base_facet_count_show;
          show_only_facets = base_facet_count_show = 4
          var plugin_id = facets[0].getAttribute('data-block-plugin-id');
          // If block plugin id contains `category`, means its category facet.
          if (plugin_id.indexOf('category') !== -1) {
            // If category facet present. then index check increases.
            show_only_facets += 1;
            total_facets -= 1;
          }
          facets.each( function (index) {
            if (index >= show_only_facets) {
              $(this).addClass('hide-facet-block');
            }
          });

          // Hide the `all filters` panel when less filters only for desktop.
          if (total_facets <= base_facet_count_show) {
            $('.show-all-filters').addClass('hide-for-desktop');
          }
          else {
            $('.show-all-filters').removeClass('hide-for-desktop');
          }
        }
      }

      /**
       * Add sliding event handlers and active class for facets.
       */
      function addSlideEventhandlers() {
        // Add active classes on facet dropdown content.
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
            // Check if sort by is open, else close it.
            if ($('.c-content .region__content .container-without-product .views-exposed-form.bef-exposed-form').find('legend').hasClass('active')) {
              $(this).removeClass('active');
              $(this).siblings('.fieldset-wrapper').slideUp();
            }
            $(this).addClass('active');
            if (!$(this).parent().parent().hasClass('filter__inner')) {
              $(this).siblings('ul').slideDown();
            }
          }
        });

        var sortSelector = '.c-content__region .region__content .container-without-product .bef-exposed-form legend';
        $(sortSelector).once().on('click', function () {
          $(this).toggleClass('active');
          if ($(this).parents('.filter__inner').length === 0) {
            $(this).siblings('.fieldset-wrapper').slideToggle();
          }
        });

        // Close the sort and facets on click outside of them.
        document.addEventListener('click', function(event) {
          var sortBy = $('.c-content .region__content .container-without-product .views-exposed-form.bef-exposed-form').first();
          if ($(sortBy).find(event.target).length == 0) {
            $(sortBy).find('legend').removeClass('active');
            $(sortBy).find('.fieldset-wrapper').slideUp();
          }

          var facet_block = $('.c-content .region__content .container-without-product div.block-facets-ajax');
          if ($(facet_block).find(event.target).length == 0) {
            $(facet_block).find('.c-facet__title').removeClass('active');
            $(facet_block).find('ul').slideUp();
          }
        });
      }

      // Function to call in ajax command on facet refresh.
      // @see AlshayaSearchAjaxController::ajaxFacetBlockView()
      $.fn.refreshListGridClass = function () {
        if ($('.grid-buttons .large-col-grid').hasClass('active')) {
          $('.c-products-list').removeClass('product-small');
          $('.c-products-list').addClass('product-large');
        }
        else {
          $('.c-products-list').removeClass('product-large');
          $('.c-products-list').addClass('product-small');
        }

        var active_facet_sort = $('#all-filter-active-facet-sort').val();
        $('.all-filters .bef-exposed-form, .all-filters .block-facets-ajax').removeClass('show-facet');
        if (active_facet_sort.length > 0) {
          $('.all-filters .bef-exposed-form, .all-filters .block-facets-ajax').hide();
          $('.all-filters #' + active_facet_sort).addClass('show-facet');
          $('.all-filters #' + active_facet_sort).show();
        }

        // If no category facet after ajax selection, add class to identify it.
        if ($('.all-filters #block-categoryfacetplp:not(:empty)').length === 0) {
          $('#block-alshaya-plp-facets-block-all').addClass('empty-category');
        }
        else {
          $('#block-alshaya-plp-facets-block-all').removeClass('empty-category');
        }

        // If there any active facet filter.
        updateFacetTitlesWithSelected();
        updateCategoryTitle();
        showFilterCount();
      };

      /**
       * Wrapping all the filters inside a div to make it sticky.
       */
      function stickyfacetwrapper() {
        if ($('.show-all-filters').length > 0) {
          if ($(window).width() > 767) {
            if ($('.c-content .container-without-product').length < 1) {
              var site_brand = $('.site-brand-home').clone();
              $('#block-subcategoryblock, .region__content > .block-facets-ajax, .region__content > .views-exposed-form, .show-all-filters').once('bind-events').wrapAll("<div class='sticky-filter-wrapper'><div class='container-without-product'></div></div>");
              $(site_brand).insertBefore('.container-without-product');
            }
          }
          else {
            if ($('.region__content > .all-filters').length < 1) {
              $('.all-filters').insertAfter('#block-page-title');
            }
          }
        }
      }

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
            filterposition = $('.sticky-filter-wrapper').offset().top + $('.sticky-filter-wrapper').height();
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

        // Sticky header on mobile view port with banner.
        if ($(window).width() < 768) {
          position = $('.region__banner-top').outerHeight();

          // Making sticky filters after category filter selection.
          if (filter.hasClass('filter-fixed-top')) {
            $('.show-all-filters').parent().css('top', fixedNavHeight);
            $('.filter-fixed-top > .block-facet-blockcategory-facet-plp, .filter-fixed-top > .block-facet-blockcategory-facet-promo, .filter-fixed-top > .block-facet-blockcategory-facet-search').css('top', fixedNavHeight);
          }
          else {
            $('.show-all-filters').parent().css('top', 0);
            $('.region__content > .block-facet-blockcategory-facet-plp, .region__content > .block-facet-blockcategory-facet-promo, .region__content > .block-facet-blockcategory-facet-search').css('top', '0');
          }
        }

        $(window).once('lhnStickyFilters').on('scroll', function () {
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

          // Sticky primary header on mobile.
          if ($(window).width() < 768) {
            if ($(this).scrollTop() > position) {
              nav.addClass('navbar-fixed-top');
              $('body').addClass('header--fixed');
            }
            else {
              nav.removeClass('navbar-fixed-top');
              $('body').removeClass('header--fixed');
            }

            if (filter.hasClass('filter-fixed-top')) {
              $('.show-all-filters').parent().css('top', fixedNavHeight);
              $('.filter-fixed-top > .block-facet-blockcategory-facet-plp, .filter-fixed-top > .block-facet-blockcategory-facet-promo, .filter-fixed-top > .block-facet-blockcategory-facet-search').css('top', fixedNavHeight);
            }
            else {
              $('.show-all-filters').parent().css('top', 0);
              $('.region__content > .block-facet-blockcategory-facet-plp, .region__content > .block-facet-blockcategory-facet-promo, .region__content > .block-facet-blockcategory-facet-search').css('top', '0');
            }
          }

          if ($(window).width() < 1024) {
            if (filter.hasClass('filter-fixed-top') && $('body').hasClass('header-sticky-filter')) {
              if (this.oldScroll > this.pageYOffset) {
                // Action to perform when we scrolling up.
                if (!$('#block-subcategoryblock').hasClass('mobile-sticky-sub-category')) {
                  $('#block-subcategoryblock').addClass('mobile-sticky-sub-category');
                }
              } else {
                // Action to perform when we are scrolling down.
                if ($('#block-subcategoryblock').hasClass('mobile-sticky-sub-category')) {
                  $('#block-subcategoryblock').removeClass('mobile-sticky-sub-category');
                }
              }
            }
            else {
              if ($('#block-subcategoryblock').hasClass('mobile-sticky-sub-category')) {
                $('#block-subcategoryblock').removeClass('mobile-sticky-sub-category');
              }
            }
            this.oldScroll = this.pageYOffset;
          }
          else {
            if (filter.hasClass('filter-fixed-top') && $('body').hasClass('header-sticky-filter') && $('body').hasClass('subcategory-listing-enabled')) {
              if (this.oldScroll > this.pageYOffset) {
                // Action to perform when we scrolling up.
                if (!$('.sticky-filter-wrapper').hasClass('show-sub-category')) {
                  $('.sticky-filter-wrapper').addClass('show-sub-category');
                }
              } else {
                // Action to perform when we are scrolling down.
                if ($('.sticky-filter-wrapper').hasClass('show-sub-category')) {
                  $('.sticky-filter-wrapper').removeClass('show-sub-category');
                }
              }
            } else {
              if ($('.sticky-filter-wrapper').hasClass('show-sub-category')) {
                $('.sticky-filter-wrapper').removeClass('show-sub-category');
              }
            }
            this.oldScroll = this.pageYOffset;
          }
        });
      }

      addSlideEventhandlers();

      if ($(window).width() < 768 && $('.filter-fixed-top').length > 0) {
        stickyfacetfilter();
      }

      $(window, context).on('load', function () {
        // Calculate the filters top position now.
        stickyfacetfilter();
      });
    }
  };

  /**
   * Scroll page to page title on selection of any of the facet item on PLPs except panty-guide.
   */
  Drupal.behaviors.alshayaAcmPlpFilterSelectionScroll = {
    attach: function () {
      var filterScrollPosition;

      $(window).once('plp-filter-selection').on('load', function () {
        // To get the offset top of plp Title, using title offset top.
        var pageTitleOffset = $('#block-page-title').offset().top;
        var brandingMenu = $('.branding__menu').height();
        if($(window).width() < 768) {
          var superCategoryMenuHeight = 0;
          var mobileNavigationMenuHeight = 0;
          if ($('.block-alshaya-super-category-menu').length > 0) {
            superCategoryMenuHeight = $('.block-alshaya-super-category-menu').height();
            mobileNavigationMenuHeight = $('.menu--mobile-navigation').height();
          }
          filterScrollPosition = pageTitleOffset - superCategoryMenuHeight - mobileNavigationMenuHeight - brandingMenu;
        }
        else {
          filterScrollPosition = pageTitleOffset;
        }
        // on window onload so that it will check for subcategory-listing-enabled class
        // after page load only
        if ($('.subcategory-listing-enabled').length < 1) {
          Drupal.AjaxCommands.prototype.viewsScrollTop = function (ajax, response) {
            var offset = $(response.selector).offset();

            var scrollTarget = response.selector;
            while ($(scrollTarget).scrollTop() === 0 && $(scrollTarget).parent()) {
              scrollTarget = $(scrollTarget).parent();
            }

            if (offset.top - 10 < $(scrollTarget).scrollTop()) {
              $(scrollTarget).animate({ scrollTop: filterScrollPosition }, 500);
            }
          };
        }
      });
    }
  };

  /**
   * Update the facet titles with the selected value.
   */
  function updateFacetTitlesWithSelected() {
    // Iterate over each facet block.
    $('.all-filters .block-facets-ajax').each(function() {
      var facet_block = $(this);
      var new_title = '';
      var total_selected = 0;
      var facets_to_show_in_label = 2;
      // If any facet item active.
      var active_facets = $(facet_block).find('ul li.is-active a span.facet-item__value');
      $.each(active_facets, function(index, element) {
        total_selected = total_selected + 1;
        // Show only two facets in title.
        if (total_selected <= facets_to_show_in_label) {
          var active_facet_label = $(element).contents().not($('.facet-item__count')).text().trim();
          new_title += active_facet_label + ', ';
        }
      });

      // Prepare the new title.
      var span_facet_title = '';
      var count_span = '';
      if (new_title.length > 0) {
        // Remove last `,` from the string.
        new_title = new_title.slice(0, -2);
        // Prepare the count span.
        if (total_selected > facets_to_show_in_label) {
          total_selected = total_selected - facets_to_show_in_label;
          count_span = '<span class="total-count"> (+' + total_selected + ')</span>';
        }
        span_facet_title = '<span class="selected-facets"><span class="title">' + new_title + '</span>' + count_span + '</span>';
      }

      // Append new title and count to facet title.
      var element_append = span_facet_title;
      $(facet_block).find('h3').find('.selected-facets').remove();
      $(facet_block).find('h3').find('.total-count').remove();
      $(facet_block).find('h3').append(element_append);
    });
  }

  /**
   * Update the category facet title on selection.
   */
  function updateCategoryTitle() {
    $('.category-facet').each(function() {
      var active_cat_facet = $(this).find('ul li.is-active');
      if ($(active_cat_facet).length > 0) {
        var facet = $(active_cat_facet).find('label span.facet-item__value');
        if (!$(active_cat_facet).hasClass('category-all')) {
          var active_cat_label = $(facet).contents().not($('.facet-item__count')).text().trim();
          $(this).find('h3').html('<span class="cateogry-active-title">' + active_cat_label + '</span>');
        }
      }
    });
  }

  // Update sort title on sort change/selection.
  function updateSortTitle() {
    // Get selected sort radio id.
    var selected_sort = $('.all-filters [data-drupal-selector="edit-sort-bef-combine"] [name="sort_bef_combine"]:checked').attr('id');
    // Get the label for the radio button.
    var for_label = $('.all-filters [data-drupal-selector="edit-sort-bef-combine"] label[for="' + selected_sort +'"]').text();
    var sort_label = '<span class="sort-for-label">' + for_label + '</span>';
    $('.all-filters [data-drupal-selector="edit-sort-bef-combine"] .fieldset-legend span.sort-for-label').remove();
    $('.all-filters [data-drupal-selector="edit-sort-bef-combine"] .fieldset-legend').append(sort_label);
  }

})(jQuery, Drupal);
