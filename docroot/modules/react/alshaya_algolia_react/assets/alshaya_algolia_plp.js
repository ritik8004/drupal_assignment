/**
 * @file
 * PLP All Filters Panel & Facets JS file.
 */
(function ($, Drupal) {

  Drupal.behaviors.alshayaAlgoliaReactPLP = {
    attach: function (context, settings) {
      var effectContext = $('#alshaya-algolia-plp');
      Drupal.algoliaReactPLP.facetEffects(effectContext);
    }
  };

  Drupal.algoliaReactPLP = Drupal.algoliaReactPLP || {};

  // Trigger events when Algolia finishes loading search results.
  Drupal.algoliaReactPLP.triggerResultsUpdatedEvent = function(results) {
    $('#alshaya-algolia-plp').trigger('plp-results-updated', [results]);

    if ($('.block-facet-blockcategory-facet-plp li').length === 0) {
      $('#alshaya-algolia-plp .show-all-filters-algolia').addClass('empty-category');
      $('.block-facet-blockcategory-facet-plp').addClass('hidden');
    }
    else {
      $('#alshaya-algolia-plp .show-all-filters-algolia').removeClass('empty-category');
      $('.block-facet-blockcategory-facet-plp').removeClass('hidden');
    }
  };

  // Show all filters blocks.
  Drupal.algoliaReactPLP.facetEffects = function (context) {
    // On clicking facet block title, update the title of block and hide
    // other facets.
    $('.all-filters-plp-algolia .c-collapse-item', context).once('algolia-plp').on('click', function() {
      var all_filters = $(this).parents('.all-filters-plp-algolia');
      // Update the title on click of facet.
      var facet_title = $(this).find('h3.c-facet__title').html();
      $('.filter-sort-title', all_filters).html(facet_title);

      // Only show current facet and hide all others.
      $(this).removeClass('show-facet');
      $('.all-filters-plp-algolia .c-collapse-item').hide();
      $(this).addClass('show-facet');

      // Show the back button.
      $('.back-facet-list', all_filters).show();
      // Update the the hidden field with the id of selected facet.
      all_filters.parent().find('#all-filter-active-facet-sort').val($(this).attr('id'));
    });

    // On clicking on back button, reset the block title and add class so
    // that facet blocks can be closed.
    $('.all-filters-plp-algolia .back-facet-list', context).once('algolia-plp').on('click', function() {
      var all_filters = $(this).parents('.all-filters-plp-algolia');
      $('.c-collapse-item', all_filters).children('ul').hide();
      $(this).hide();
      $('.filter-sort-title', all_filters).html(Drupal.t('filter & sort'));
      $('.c-collapse-item', all_filters).removeClass('show-facet');
      $('.c-collapse-item', all_filters).not('.hide-facet-block').show();
      $('.c-collapse-item .c-facet__title', all_filters).removeClass('active');
      // Reset the hidden field value.
      all_filters.parent().find('#all-filter-active-facet-sort').val('');
    });

    // Grid switch for PLP and Search pages.
    $('.small-col-grid', context).once('algolia-plp').on('click', function () {
      var isActive = $(this).hasClass('active');
      $('.large-col-grid', context).removeClass('active');
      $(this).addClass('active');
      $('body').removeClass('large-grid')
      $('.c-products-list', context).removeClass('product-large').addClass('product-small');

      // Push small column grid click event to GTM.
      if (!isActive) {
        Drupal.alshayaSeoGtmPushEcommerceEvents({
          eventAction: 'plp clicks',
          eventLabel: 'plp layout - small grid',
        });
      }
    });

    $('.large-col-grid', context).once('algolia-plp').on('click', function () {
      var isActive = $(this).hasClass('active');
      $('.small-col-grid', context).removeClass('active');
      $(this).addClass('active');
      $('body').addClass('large-grid');
      $('.c-products-list', context).removeClass('product-small').addClass('product-large');

      // Push large column grid click event to GTM.
      if (!isActive) {
        Drupal.alshayaSeoGtmPushEcommerceEvents({
          eventAction: 'plp clicks',
          eventLabel: 'plp layout - large grid',
        });
      }
    });

    // Add dropdown effect for facets filters.
    $('.c-facet__title.c-collapse__title', context).once('algolia-plp').on('click', function () {
      if ($(this).hasClass('active')) {
        $(this).removeClass('active');
        // We want to run this only on main page facets.
        if (!$(this).parent().parent().hasClass('filter__inner')) {
          $(this).siblings('ul').slideUp();
        }
      }
      else {
        if (!$(this).parent().parent().parent().hasClass('filter__inner')) {
          $(this).parent().siblings('.c-facet').find('.c-facet__title.active').siblings('ul').slideUp();
          $(this).siblings('ul').slideDown();
          $(this).parent().siblings('.c-facet').find('.c-facet__title.active').removeClass('active');
        }
        $(this).addClass('active');
      }
    });

    // Update category facet label and close category accordion on selection of lowest child item
    $('#alshaya-algolia-plp').once('plpCategoryLabel').on('plp-results-updated', function () {
      var active_cat_facet = $('.block-facet-blockcategory-facet-plp').find('ul li.item--selected:not(.item--parent)');
      if ($(active_cat_facet).length > 0) {
        var facet = $(active_cat_facet).find('.facet-item span.facet-item__value');
        var active_cat_label = $(facet).contents().not($('.facet-item__count')).text().trim();
        $('.block-facet-blockcategory-facet-plp').find('h3').removeClass('active').html('<span class="cateogry-active-title">' + active_cat_label + '</span>');
        $('.block-facet-blockcategory-facet-plp > ul').slideUp();
      }
      else {
        $('.block-facet-blockcategory-facet-plp')
          .find('h3')
          .removeClass('active')
          .html(drupalSettings.algoliaSearch.category_facet_label);

        $('.block-facet-blockcategory-facet-plp > ul').slideUp();
      }
    });

    $('.sticky-filter-wrapper .show-all-filters-algolia', context).once('algolia-plp').on('click', function() {
      $('.all-filters-plp-algolia', context).addClass('filters-active');

      if ($(window).width() > 1023) {
        $('html').addClass('all-filters-overlay');
      }
      else {
        $('body').addClass('mobile--overlay');
      }

      $('.all-filters-plp-algolia .c-collapse-item', context).removeClass('show-facet');

      var active_filter_sort = $('#all-filter-active-facet-sort', context).val();
      // On clicking `all` filters, check if there was filter which selected last.
      if (active_filter_sort.length > 0) {
        $('.all-filters-plp-algolia #' + active_filter_sort, context).show();
        $('.all-filters-plp-algolia #' + active_filter_sort, context).addClass('show-facet');
      }
      else {
        $('.all-filters-plp-algolia .c-collapse__title.active', context).parent('.c-collapse-item').addClass('show-facet');
      }

      $('.all-filters-plp-algolia', context).show();
    });

    // Fake facet apply button to close the `all filter`.
    $('.all-filters-plp-algolia .all-filters-close, .all-filters-plp-algolia .facet-apply-all', context).once('algolia-plp').on('click', function() {
      $('.all-filters-plp-algolia', context).removeClass('filters-active');
      $('body').removeClass('mobile--overlay');
      $('html').removeClass('all-filters-overlay');
    });
  };

  /**
   * Make Header sticky on scroll.
   */
  Drupal.algoliaReactPLP.stickyfacetfilter = function () {
    var algoliaReactFilterPosition = 0;
    var superCategoryMenuHeight = 0;
    var nav = $('.branding__menu');
    var fixedNavHeight = 0;
    var context = $('#alshaya-algolia-plp');
    var subCategoryBlock = $('.block-alshaya-sub-category-block');
    var filter = $('#alshaya-algolia-plp');
    if ($('.show-all-filters-algolia', context).length > 0) {
      filter.find('.container-without-product').addClass('plp-facet-product-filter');
      if ($(window).width() > 1023) {
        algoliaReactFilterPosition = $('.container-without-product', context).offset().top;
      } else if ($(window).width() > 767 && $(window).width() < 1024) {
        algoliaReactFilterPosition = $('.show-all-filters-algolia', context).offset().top;
      } else {
        if ($('.block-alshaya-super-category').length > 0) {
          superCategoryMenuHeight = $('.block-alshaya-super-category').outerHeight() + $('.menu--mobile-navigation').outerHeight();
        }
        if ($('.show-all-filters-algolia', context).length > 0) {
          algoliaReactFilterPosition = $('.show-all-filters-algolia', context).offset().top - $('.branding__menu').outerHeight() - superCategoryMenuHeight;
        }
        fixedNavHeight = nav.outerHeight() + superCategoryMenuHeight;
      }
    }

    $(window).once('algoliaStickyPLPFilters').on('scroll', function () {
      // Sticky filter header.
      if ($('.show-all-filters-algolia', context).length > 0) {
        if ($(this).scrollTop() > algoliaReactFilterPosition) {
          context.addClass('filter-fixed-top');
          $('body').addClass('header-sticky-filter');
          if ($(this).width() > 767 && subCategoryBlock.length > 0) {
            if (!subCategoryBlock.hasClass('anti-ghosting') && !subCategoryBlock.hasClass('anti-ghosting-done')) {
              subCategoryBlock.addClass('anti-ghosting');
            }
          }
        } else {
          context.removeClass('filter-fixed-top');
          $('body').removeClass('header-sticky-filter');
          if (subCategoryBlock.length > 0) {
            // Desktop & Tablet.
            subCategoryBlock.removeClass('anti-ghosting-done');
          }
        }
      }

      if (subCategoryBlock.length > 0) {
        if ($(window).width() < 1024) {
          if (filter.hasClass('filter-fixed-top') && $('body').hasClass('header-sticky-filter')) {
            if (this.oldScroll > this.pageYOffset) {
              // Action to perform when we scrolling up.
              if (!subCategoryBlock.hasClass('mobile-sticky-sub-category') && subCategoryBlock.length > 0) {
                // Tablet.
                if ($(window).width() > 767) {
                  subCategoryBlock.removeClass('anti-ghosting');
                  subCategoryBlock.addClass('anti-ghosting-done');
                }
                // This small delay to ensure the entry animations works.
                setTimeout(() => {
                  subCategoryBlock.addClass('mobile-sticky-sub-category');
                }, 5);
              }
            } else {
              // Action to perform when we are scrolling down.
              if (subCategoryBlock.hasClass('mobile-sticky-sub-category')) {
                subCategoryBlock.removeClass('mobile-sticky-sub-category');
              }
            }
          } else {
            if (subCategoryBlock.hasClass('mobile-sticky-sub-category')) {
              subCategoryBlock.removeClass('mobile-sticky-sub-category');
            }
          }
          this.oldScroll = this.pageYOffset;
        } else {
          if (filter.hasClass('filter-fixed-top') && $('body').hasClass('header-sticky-filter') && subCategoryBlock.length > 0) {
            if (this.oldScroll > this.pageYOffset) {
              // Action to perform when we scrolling up.
              if (!$('.sticky-filter-wrapper').hasClass('show-sub-category')) {
                if ($(this).width() > 1024 && subCategoryBlock.length > 0) {
                  subCategoryBlock.removeClass('anti-ghosting');
                  subCategoryBlock.addClass('anti-ghosting-done');
                  // This small delay to ensure the entry animations works.
                  setTimeout(() => {
                    $('.sticky-filter-wrapper').addClass('show-sub-category');
                  }, 5);
                } else {
                  $('.sticky-filter-wrapper').addClass('show-sub-category');
                }
              }
            } else {
              if (this.oldScroll < this.pageYOffset || this.oldScroll !== this.pageYOffset) {
                // Action to perform when we are scrolling down.
                if ($('.sticky-filter-wrapper').hasClass('show-sub-category')) {
                  $('.sticky-filter-wrapper').removeClass('show-sub-category');
                }
              }
            }
          } else {
            if ($('.sticky-filter-wrapper').hasClass('show-sub-category')) {
              $('.sticky-filter-wrapper').removeClass('show-sub-category');
            }
          }
          this.oldScroll = this.pageYOffset;
        }
      }
    });
  };
})(jQuery, Drupal);
