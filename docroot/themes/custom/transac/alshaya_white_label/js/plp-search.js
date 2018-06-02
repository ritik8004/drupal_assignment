/**
 * @file
 * Search and PLP DOM modifications and event handlers.
 */

(function ($) {
  'use strict';
  Drupal.behaviors.alshayaPLPSearch = {
    attach: function (context, settings) {
      // Close the mobile filter menu on tablet if clicked anywhere else.
      $(window).once().on('click', function (e) {
        // Run only on PLP and Search page.
        if ($('.page-wrapper > .page-standard').hasClass('c-plp')) {
          if ($(window).width() >= 768) {
            if ($(e.target).is('.c-facet__label')) {
              // Do nothing in this case, we already have a handler for this.
            }
            else if ($(e.target).is('.c-facet__title')) {
              // Do nothing in this case, we already have a handler for this.
            }
            else {
              // Close the menu if it is open.
              if ($('.filter--mobile .c-facet__blocks').is(':visible')) {
                $('.page-wrapper, .header--wrapper, .c-pre-content, .c-breadcrumb, .branding__menu, .c-footer')
                  .toggleClass('show-overlay');
                $('.filter--mobile .c-facet__blocks').toggle();
                $('body').toggleClass('filter-open-no-scroll');
                $('.c-facet__blocks__wrapper .c-facet__label').toggleClass('is-active');
                $('.c-facet__blocks__wrapper .c-facet__label').parent().siblings('.view-filters').toggleClass('low-zindex');
              }
            }
          }
        }
      });

      /**
       * Get the correct selector for filter bar on PLP and search page.
       *
       * @return {*} mobileFilterBarSelector
       *   Correct selector based on which page you are on.
       */
      function getFilterBarSelector() {
        var mobileFilterBarSelector = null;
        if ($('body').hasClass('path--search')) {
          mobileFilterBarSelector = '.region__content .region__sidebar-first .block-facets-summary-blockfilter-bar';
        }
        else if ($('body').hasClass('nodetype--acq_promotion')) {
          mobileFilterBarSelector = '.region__content .region__sidebar-first .block-facets-summary-blockfilter-bar-promotions';
        }
        else {
          mobileFilterBarSelector = '.c-content__region .region__sidebar-first .block-facets-summary-blockfilter-bar-plp';
        }
        return mobileFilterBarSelector;
      }

      /**
       * Place the facet count, in the mobile filter view.
       */
      function placeFilterCount() {
        // Mobile filter block selector.
        var mobileFilterBarSelector = getFilterBarSelector();

        var countFilters = $(mobileFilterBarSelector + ' ul li').length;
        if (countFilters === 0 && $.trim($(mobileFilterBarSelector).html()).length === 0) {
          $(mobileFilterBarSelector).addClass('empty');
        }
        else {
          if (countFilters > 0) {
            // Removing the element before adding again.
            $(mobileFilterBarSelector + ' > h3').remove();
            // We need to minus one count as the facets also include clear all link.
            countFilters = countFilters - 1;
            // If there are filters applied, we need to show the count next to the label.
            $('<h3 class="applied-filter-count c-accordion__title ui-state-active">' + Drupal.t('applied filters')
              + '(' + countFilters + ')</h3>')
              .insertBefore(mobileFilterBarSelector + ' ul')
              .off()
              .on('click', function (e) {
                Drupal.alshayaAccordion(this);
              });
          }
        }
      }

      /**
       * Close the mobile filter view screen.
       */
      function closeFilterView() {
        $('body').toggleClass('filter-open-no-scroll');
        $('#backtotop').removeClass('facet-active--hide');
        $('.c-facet__blocks__wrapper--mobile .c-facet__blocks').hide();
        $('.show-overlay').each(function () {
          $(this).removeClass('show-overlay');
        });
      }

      function mobileFilterMenu() {
        // Mobile filter block selector.
        var mobileFilterBarSelector = getFilterBarSelector();
        // The original filter block.
        var filterBarSelector = null;
        if ($('body').hasClass('path--search')) {
          filterBarSelector = '.region__content > .block-facets-summary-blockfilter-bar';
        }
        else if ($('body').hasClass('nodetype--acq_promotion')) {
          filterBarSelector = '.region__content > .block-facets-summary-blockfilter-bar-promotions';
        }
        else {
          filterBarSelector = '.region__content > .block-facets-summary-blockfilter-bar-plp';
        }

        if ($(window).width() < 768) {
          // Enable & disable apply filter button on mobile.
          $(document).ajaxComplete(function () {
            var facetBlocks = $('.c-facet__blocks__wrapper--mobile .c-facet__blocks');

            if (facetBlocks.length !== 0) {
              var selectedFiterCount = facetBlocks.find('a.is-active').length;
              var fakeApplyButton = $('.fake-apply-button');
              if (selectedFiterCount > 0) {
                fakeApplyButton.parent().removeClass('inactive');
                fakeApplyButton.removeAttr('disabled');
                if (!fakeApplyButton.parent().hasClass('active')) {
                  fakeApplyButton.parent().addClass('active');
                }
              }
              else {
                fakeApplyButton.parent().removeClass('active');
                fakeApplyButton.attr('disabled', 'disabled');
                if (!fakeApplyButton.parent().hasClass('inactive')) {
                  fakeApplyButton.parent().addClass('inactive');
                }
              }
            }
          });

          // Facet Block selector.
          var facetBlocks = $('.c-facet__blocks__wrapper .c-facet__blocks');

          // Check if we have filter label.
          var filterLabel = facetBlocks.find('.filter-menu-label');
          if (filterLabel.length) {
            // This is an ajax call.
          }
          else {
            // If we dont have one, create it, this is first time load.
            $('<div class="filter-menu-label"><span class="label">' + Drupal.t('filter') + '</span><li class="apply-fake"><input type="button" class="fake-apply-button inactive" disabled value="' + Drupal.t('apply') + '"></li><span class="filter-close"></span></div>')
              .insertBefore('.region__content .c-facet__blocks .region__sidebar-first ');

            $('.fake-apply-button').click(function () {
              closeFilterView();
            });
          }

          if ($(mobileFilterBarSelector).length) {
            placeFilterCount();

            var countFilters = $(mobileFilterBarSelector + ' ul li').length - 1;
            if (countFilters > 0) {
              $('.c-facet__blocks__wrapper--mobile h3.c-facet__label').html(Drupal.t('Filter') + ' (' + countFilters + ')');
            }
          }
          else {
            // Clone the filter block from region content.
            var blockFilterBar = $(filterBarSelector).clone();

            // Place the cloned bar before other facets in the region content's sidebar first.
            $(blockFilterBar)
              .insertBefore('.region__content .c-facet__blocks .region__sidebar-first > div:first-child');

            placeFilterCount();
          }

          // Hide the filter block in mobile.
          $(filterBarSelector).hide();
        }
        else {
          // Show the filter block in the content region for tablet and desktop.
          $(filterBarSelector).show();
        }
      }

      /**
       * Place the search count from view header in different locations based on resolution.
       */
      function placeSearchCount() {
        var viewHeader = null;
        var selector = null;
        if ($('body').hasClass('path--search')) {
          viewHeader = $('.c-search .view-search .view-header');
          selector = '.c-content__region .block-views-exposed-filter-blocksearch-page';
        }
        else if ($('body').hasClass('nodetype--acq_promotion')) {
          viewHeader = $('.c-plp .view-alshaya-product-list .view-header');
          selector = '.c-content__region .block-views-exposed-filter-blockalshaya-product-list-block-2';
        }
        else {
          viewHeader = $('.c-plp .view-alshaya-product-list .view-header');
          selector = '.c-content__region .block-views-exposed-filter-blockalshaya-product-list-block-1';
        }
        viewHeader.addClass('search-count');
        var searchCount = $('.c-content__region .search-count');
        // For mobile.
        if ($(window).width() < 768) {
          $('.block-page-title-block').addClass('mobile');
          searchCount.removeClass('tablet');
          if (viewHeader.length) {
            searchCount.remove();
            viewHeader.insertBefore(selector);
          }
          searchCount.addClass('only-mobile');
        }
        // For tablet and desktop.
        else {
          $('.block-page-title-block').removeClass('mobile');
          searchCount.removeClass('only-mobile');
          if (viewHeader.length) {
            searchCount.remove();
            viewHeader.insertBefore(selector);
          }
          searchCount.addClass('tablet');
        }
      }

      function processSoftLiniks(element) {
        var softLink = element.find('a.facets-soft-limit-link');
        var blockPlugin = element.attr('data-block-plugin-id');
        var facet_id = blockPlugin.replace('facet_block:', '');
        var softLimitSettings = settings.facets.softLimit;
        var softItemsLimit = softLimitSettings[facet_id] - 1;
        if (!isNaN(parseInt(softItemsLimit))) {
          // Facets module would hide all instances of list items in the
          // second instance of the facet block. This is to support same
          // facet block twice on a page.
          element.find('ul li:lt(' + (parseInt(softItemsLimit) + 1) + ')').show();
          element.find('ul li:gt(' + parseInt(softItemsLimit) + ')').hide();
          softLink.insertAfter(element.find('ul'));
        }
      }

      if (context === document) {
        if ($('.c-facet__blocks__wrapper').length) {
          var facetBlockWrapper = $('.c-facet__blocks__wrapper')
            .clone(true, true);
          var mainBlock = $('.block-system-main-block');
          var facetLabel = facetBlockWrapper.find('.c-facet__label');
          var facetBlock = facetBlockWrapper.find('.c-facet__blocks');

          facetBlockWrapper.addClass('c-facet__blocks__wrapper--mobile')
            .addClass('is-filter');
          if ($('body').hasClass('path--search')) {
            mainBlock.before(facetBlockWrapper);
            var searchFilter = $('.c-plp #views-exposed-form-search-page');
            searchFilter.wrapAll('<div class="view-filters is-filter">');
            $('.is-filter').wrapAll('<div class="filter--mobile clearfix">');
            $('.region__content .block-views-exposed-filter-blocksearch-page .c-facet__blocks__wrapper')
              .insertBefore('.view-filters.is-filter');
          }
          else if ($('body').hasClass('nodetype--acq_promotion')) {
            mainBlock.before(facetBlockWrapper);
            var promoFilter = $('.c-plp #views-exposed-form-alshaya-product-list-block-2');
            promoFilter.wrapAll('<div class="view-filters is-filter">');
            $('.is-filter').wrapAll('<div class="filter--mobile clearfix">');
            $('.region__content .c-facet__blocks__wrapper')
              .insertBefore('.view-filters.is-filter');
          }
          else {
            mainBlock.before(facetBlockWrapper);
            var plpFilter = $('.c-plp #views-exposed-form-alshaya-product-list-block-1');
            plpFilter.wrapAll('<div class="view-filters is-filter">');
            $('.is-filter').wrapAll('<div class="filter--mobile clearfix">');
            $('.region__content .c-facet__blocks__wrapper')
              .insertBefore('.view-filters.is-filter');
          }

          facetLabel.on('click', function () {
            $('.page-wrapper, .header--wrapper, .c-pre-content, .c-breadcrumb, .branding__menu, .c-footer')
              .toggleClass('show-overlay');
            facetLabel.toggleClass('is-active');
            $('body').toggleClass('filter-open-no-scroll');
            $('#backtotop').addClass('facet-active--hide');
            facetLabel.parent().siblings('.view-filters').toggleClass('low-zindex');
            facetBlock.toggle();
          });
        }

        // Hiding the filter border if there are no filters.
        var checkFilter = $.trim($('.c-search .region__content .block-facets-summary-blockfilter-bar')
          .html());
        if (checkFilter.length) {
          $('.c-search .region__content .block-facets-summary-blockfilter-bar')
            .css('border-bottom-width', '1px');
        }
        else {
          $('.c-search .region__content .block-facets-summary-blockfilter-bar')
            .css('border-bottom-width', '0');
        }

        // Add class to promotional banner view block if it is not empty.
        var bannerBlock = '';
        if ($('body').hasClass('nodetype--acq_promotion')) {
          if (!$('.view-promotion-banner .field-content').is(':empty')) {
            bannerBlock = $('.block-views-blockpromotion-banner-block-1');
            bannerBlock.addClass('promo-banner');
            $('.region__content').addClass('promo-banner');
            bannerBlock.siblings('.block-views-exposed-filter-blockalshaya-product-list-block-2').addClass('promo-banner');
          }
        }
        else {
          if (!$('.view-plp-promotional-banner .field-content').is(':empty')) {
            bannerBlock = $('.block-views-blockplp-promotional-banner-block-1');
            bannerBlock.addClass('promo-banner');
            $('.region__content').addClass('promo-banner');
            bannerBlock.siblings('.block-views-exposed-filter-blockalshaya-product-list-block-1').addClass('promo-banner');
          }
        }
      }

      // Clone the filter bar and add it to the filter menu on mobile.
      // Show mobile slider only on mobile resolution.
      mobileFilterMenu();
      placeSearchCount();
      $(window).on('resize', function (e) {
        mobileFilterMenu();
        placeSearchCount();
      });

      // Toggle the filter menu when click on the label.
      $('.filter-menu-label .label').once().on('click', function () {
        $('.c-facet__blocks__wrapper .c-facet__label').parent().siblings('.view-filters').toggleClass('low-zindex');
        $('.c-facet__blocks__wrapper .c-facet__label').toggleClass('is-active');
        $('.c-facet__blocks__wrapper .c-facet__blocks').toggle();
        $('body').toggleClass('filter-open-no-scroll');
        $('.show-overlay').each(function () {
          $(this).removeClass('show-overlay');
        });
      });

      $('.c-facet').each(function () {
        if ($(this).hasClass('facet-active')) {
          $(this).find('.c-accordion__title').addClass('ui-state-active');
        }
      });

      // Close button to close the mobile filter view.
      $('span.filter-close', context).stop().on('click', function () {
        closeFilterView();
      });

      // Process facet checbox softlimits on page load.
      $('.block-facet--checkbox', context).each(function () {
        processSoftLiniks($(this));
      });

      // Process facet checbox softlimits while rebuilding facets post AJAX.
      if ($(context).hasClass('block-facet--checkbox')) {
        processSoftLiniks($(context));
      }
    }
  };
})(jQuery);
