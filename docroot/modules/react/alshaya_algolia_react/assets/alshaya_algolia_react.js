/**
 * @file
 * All Filters Panel & Facets JS file.
 */
(function ($, Drupal) {

  // Variable for sale item index in category search filter
  var sale_index = null;

  Drupal.behaviors.alshayaAlgoliaReact = {
    attach: function (context, settings) {
      // Variable to determine facet-item level in category block facet.
      var facet_item_level;

      $(window).on('load', function(event) {
        $('body').once('algolia-search').on('click','.sticky-filter-wrapper .c-collapse-item .facet-item', function(event) {
          // All facets except category search block.
          if($(this).parents('.block-facet-blockcategory-facet-search').length === 0) {
            $(this).parents('.c-facet.c-collapse-item').find('.c-facet__title.c-collapse__title.active').trigger('click');
          }
          else {
            facet_item_level = $(this).data('level');
            var curr_index = $(this).parent().index();
            // To check if not deselecting the sale item and not selecting any sale child.
            if (sale_index !== null && curr_index !== sale_index && facet_item_level === 1) {
              sale_index = null;
            }
          }
        });
      });

      // After Algolia search results have been updated from category search filter, check if L1 has child-items.
      // Close accordion if it doesn't has children.
      $(document).once('updatedAlgoliaResults').on('search-results-updated', '#alshaya-algolia-search', function () {
        var category_facet_search_block = $('.block-facet-blockcategory-facet-search');
        var active_facet = category_facet_search_block.find("[data-level='" + facet_item_level + "'].facet-item.is-active");
        var category_dropdown_height_scroll = category_facet_search_block.find('ul').first().scrollTop();
        var facet_item_height = category_facet_search_block.find('ul').first().find('li:first-child a').outerHeight();
        if ($(window).width() < 1025) {
          if (active_facet.siblings('ul').length === 0) {
            // Close category accordion if not deselecting sale item or selection any sale child.
            if (sale_index === null || facet_item_level > 1) {
              category_facet_search_block.find('.c-facet__title.c-collapse__title.active').trigger('click');
            }
            // Scroll category dropdown to previous value.
            else {
              category_facet_search_block.find('ul').first().scrollTop(category_dropdown_height_scroll);
            }
          }
          else {
            var child = active_facet.siblings('ul');
            sale_index = child.parent().index();
            var category_height = category_facet_search_block.outerHeight();
            var category_height_offset = category_facet_search_block.offset().top;
            var category_dropdown_height = category_facet_search_block.find('ul').first().outerHeight();
            var calc_offset = category_height + category_height_offset + category_dropdown_height;

            // If sale item children is not in view, scroll to show children.
            if (child.offset().top > (calc_offset - (facet_item_height * 1.5))) {
              // For mobile portrait, scroll to show 3.5 items so that user will know there are more items to scroll if any.
              if ($(window).height() > 480) {
                category_facet_search_block.find('ul').first().scrollTop(category_dropdown_height_scroll + (facet_item_height * 3.5));
              }
              // For mobile landscape, scroll to show 1.5 items so that user will know there are more items to scroll if any.
              else {
                category_facet_search_block.find('ul').first().scrollTop(category_dropdown_height_scroll + (facet_item_height * 1.5));
              }
            }
          }
        }
      });

      if ($('#alshaya-algolia-search').length > 0) {
        Drupal.algoliaReact.facetEffects();
      }
    }
  };

  Drupal.algoliaReact = Drupal.algoliaReact || {};

  // Trigger events when Algolia finishes loading search results.
  Drupal.algoliaReact.triggerSearchResultsUpdatedEvent = function(resultCount) {
    $('#alshaya-algolia-search').trigger('search-results-updated', [resultCount]);
  };

  // Show all filters blocks.
  Drupal.algoliaReact.facetEffects = function () {
    var context = $('#alshaya-algolia-search');
    // On clicking facet block title, update the title of block and hide
    // other facets.
    $('.all-filters-algolia .c-collapse-item', context).once('algolia-search').on('click', function() {
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
    $('.all-filters-algolia .back-facet-list', context).once('algolia-search').on('click', function() {
      var all_filters = $(this).parents('.all-filters-algolia');
      $('.c-collapse-item', all_filters).find('>ul').hide();
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
      var isActive = $(this).hasClass('active');
      $('.large-col-grid', algolia_wrapper).removeClass('active');
      $(this).addClass('active');
      $('body').removeClass('large-grid')
      $('.c-products-list', algolia_wrapper).removeClass('product-large').addClass('product-small');

      // Push small column grid click event to GTM.
      if (!isActive) {
        Drupal.alshayaSeoGtmPushEcommerceEvents({
          eventAction: 'plp clicks',
          eventLabel: 'plp layout - small grid',
        });
      }
    });

    $('#alshaya-algolia-search .large-col-grid').once('algolia-search').on('click', function () {
      var algolia_wrapper = $(this).parents('#alshaya-algolia-search');
      var isActive = $(this).hasClass('active');
      $('.small-col-grid', algolia_wrapper).removeClass('active');
      $(this).addClass('active');
      $('body').addClass('large-grid');
      $('.c-products-list', algolia_wrapper).removeClass('product-small').addClass('product-large');

      // Push large column grid click event to GTM.
      if (!isActive) {
        Drupal.alshayaSeoGtmPushEcommerceEvents({
          eventAction: 'plp clicks',
          eventLabel: 'plp layout - large grid',
        });
      }
    });

    // Add dropdown effect for facets filters.
    if (drupalSettings.superCategory && ($(window).width() > 1023)) {
      $('.block-facet-blockcategory-facet-search .c-facet__title.c-collapse__title', context).addClass('active');
    }

    if ($(window).width() > 1024) {
      $('.block-facet-blockcategory-facet-search .c-facet__title.c-collapse__title', context).addClass('active');
    }

    $('.c-facet__title.c-collapse__title', context).once('algolia-search').on('click', function () {
      if ($(this).hasClass('active')) {
        $(this).removeClass('active');
        // We want to run this only on main page facets.
        if (!$(this).parent().parent().hasClass('filter__inner')) {
          $(this).siblings('ul').slideUp();
        }
      }
      else {
        if (!$(this).parent().parent().hasClass('filter__inner') && !$(this).parents().hasClass('c-sidebar-first')) {
          $(this).parent().siblings('.c-facet').find('.c-facet__title.active').siblings('ul').slideUp();
          $(this).parent().siblings('.c-facet').find('.c-facet__title.active').removeClass('active');
        }

        $(this).addClass('active');
        if (!$(this).parent().parent().hasClass('filter__inner')) {
          $(this).siblings('ul').slideDown();
        }
      }
    });

    $('.sticky-filter-wrapper .show-all-filters-algolia', context).once('algolia-search').on('click', function() {
      $('.all-filters-algolia', context).addClass('filters-active');

      if ($(window).width() > 1023) {
        $('html').addClass('all-filters-overlay');
      }
      else {
        $('body').addClass('mobile--overlay');
      }

      $('.all-filters-algolia .c-collapse-item', context).removeClass('show-facet');

      var active_filter_sort = $('#all-filter-active-facet-sort', context).val();
      // On clicking `all` filters, check if there was filter which selected last.
      if (active_filter_sort.length > 0) {
        $('.all-filters-algolia #' + active_filter_sort, context).show();
        $('.all-filters-algolia #' + active_filter_sort, context).addClass('show-facet');
      }
      else {
        $('.all-filters-algolia .c-collapse__title.active', context).parent('.c-collapse-item').addClass('show-facet');
      }

      $('.all-filters-algolia', context).show();
    });

    $(window).once('reset-filter-class').on('hashchange', function() {
      $('html').removeClass('all-filters-overlay');
      $('body').removeClass('mobile--overlay');
    });

    // Fake facet apply button to close the `all filter`.
    $('.all-filters-algolia .all-filters-close, .all-filters-algolia .facet-apply-all', context).once('algolia-search').on('click', function() {
      $('.all-filters-algolia', context).removeClass('filters-active');
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

    if ($('#alshaya-algolia-search .show-all-filters-algolia').length > 0) {
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

    if ($('#alshaya-algolia-search.show-algolia-result').length > 0) {
      $(window).once('algoliaStickyFilter').on('scroll', function () {
        // Sticky filter header.
        if ($('#alshaya-algolia-search .show-all-filters-algolia').length > 0 && $('#alshaya-algolia-search.show-algolia-result').length > 0) {
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

  Drupal.behaviors.searchSizeGroupFilter = {
    // Opens the selected grand parent filter value using
    // #all-filter-active-facet-sort text value.
    openSelectedSizeGroupFilter: function() {
      var active_facet_sort = $('#all-filter-active-facet-sort').val();
      if (!active_facet_sort || (active_facet_sort && active_facet_sort.length === 0)) {
        return;
      }
      var active_facet_sort_elements = active_facet_sort.split(','); // convert to array of values

      if(active_facet_sort_elements.length > 1) {
        for(var i = 0; i < active_facet_sort_elements.length ; i++) {
          // Normal execution in case of the facet block selector.
          if($('.all-filters-algolia #' + active_facet_sort_elements[i]).hasClass('c-facet')) {
            $('.all-filters-algolia #' + active_facet_sort_elements[i]).addClass('show-facet');
          } else { // Add class to the parent of selected children if the selector is not c-facet.
            $('.all-filters-algolia .size_group_list >ul >li').hide();
            $('.all-filters-algolia [id=' + active_facet_sort_elements[i] + ']').parent().addClass('show-facet');
          }
        }
      }
    },

    attach: function (context, settings) {
      // Attach on attach behaviour call.
      Drupal.behaviors.searchSizeGroupFilter.openSelectedSizeGroupFilter();

      // Override reopen facet logic.
      $('.sticky-filter-wrapper').once('facet-show-all-for-search-sizegroup-processed').on('click', '.show-all-filters-algolia', function() {
        Drupal.behaviors.searchSizeGroupFilter.openSelectedSizeGroupFilter();
      });

      $('.sticky-filter-wrapper').once('product-option-show-search-size-filter').on('click tap', '.size_group_list ul li', function (context) {
        var $oldOpenedEl = $('.is-open', $(this).parent());
        if($(this).hasClass('is-open')) {
          $oldOpenedEl.removeClass('is-open');
          return;
        }

        $oldOpenedEl.removeClass('is-open');
        $(this).addClass('is-open');
      });

      $('.all-filters-algolia').on('click', '.size_group_list >ul >li', function (event) {
        // Update the title on click of facet.
        var facet_title = $(event.target).text();

        if($(event.target).closest('.sizegroup').length > 0) {
          facet_title = $(event.target).closest('.sizegroup').parent().children('span').text();
          $('.filter-sort-title').html(facet_title);

          // Return for normal execution.
          return;
        }

        $('.filter-sort-title').html(facet_title);
        // Only show current facet and hide all others.
        $(this).removeClass('show-facet');
        $('.all-filters-algolia .size_group_list >ul >li').hide();
        $(this).addClass('show-facet');
        // Pass comma separated ids for the elements to make visible.
        // This is separate by , so that other JS logic bypasses it
        // and this will be handled by this behaviour specifically
        // See openSelectedSizeGroupFilter function.
        $('#all-filter-active-facet-sort', $(this).closest('.filter__inner').closest('.block')).val($(this).children('ul').attr('id') + ',' + $(this).closest('.c-facet').attr('id'));

        // Stop event bubbling and normal execution.
        event.stopPropagation();
        event.preventDefault();
      });

      $('.all-filters-algolia').once('facet-all-back-for-search-sizegroup-processed').on('click', '.back-facet-list', function() {
        var $selectedGrandChild = $('.all-filters-algolia .size_group_list >ul >li.show-facet');

        // If any grand child is open go to this custom logic.
        if($selectedGrandChild.length) {
          // Reset what is done in the above click handler to reset the state.
          $('.all-filters-algolia .size_group_list >ul >li').show();
          // Select it's parent (child of main grand parent) and trigger click on it.
          // This is done in order to mimmick the back state.
          $selectedGrandChild.closest('.c-facet').find('.c-facet__title').trigger('click');
          // Reset the show facet class to reset to original state.
          $('.all-filters-algolia .size_group_list >ul >li.show-facet').removeClass('show-facet');
        }

        // Stop event bubbling and normal execution.
        event.stopPropagation();
        event.preventDefault();
      });
    }
  }

})(jQuery, Drupal);
