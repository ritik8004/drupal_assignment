/**
 * @file
 * Search and PLP DOM modifications and event handlers.
 */

(function ($) {
  'use strict';
  Drupal.behaviors.alshayaPLPSearch = {
    attach: function (context, settings) {
      // Close the mobile filter menu on tablet if clicked anywhere else.
      $(document).once().on('click', function (e) {
        // Run only on PLP and Search page.
        if ($('.page-wrapper > .page-standard').hasClass('c-plp')) {
          if ($(window).width() >= 768) {
            if ($(e.target).is('.c-facet__label') || $(e.target).is('.facets-search-input')) {
              // Do nothing in this case, we already have a handler for this.
            }
            else if ($(e.target).is('.c-facet__title')) {
              // Do nothing in this case, we already have a handler for this.
            }
            else {
              // Close the menu if it is open.
              if ($('.filter--mobile .c-facet__blocks').is(':visible')) {
                $('.page-wrapper, .header--wrapper, .c-pre-content, .plp-video, .c-breadcrumb, .branding__menu, .region__banner-top, .c-footer')
                  .toggleClass('show-overlay');
                // Turn of transition for some regions.
                setTimeout(function () {
                  $('.branding__menu').toggleClass('no-animate');
                }, 500);
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
       * Close the mobile filter view screen.
       */
      function closeFilterView() {
        $('body').toggleClass('filter-open-no-scroll');
        $('#backtotop').removeClass('facet-active--hide');
        $('.c-facet__blocks__wrapper--mobile .c-facet__blocks').hide();
        $('.show-overlay').each(function () {
          $(this).removeClass('show-overlay');
          $(this).removeClass('no-animate');
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

          var countFilters = $(mobileFilterBarSelector + ' ul li').length - 1;
          if (countFilters > 0) {
            $('.c-facet__blocks__wrapper--mobile h3.c-facet__label').addClass('active-filter-count').html(Drupal.t('Filter') + ' <span class="filter-count"> ' + countFilters + '</span>');
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
          selector = $('.c-content__region .total-result-count, .facet-all-count');
        }
        else if ($('body').hasClass('nodetype--acq_promotion')) {
          viewHeader = $('.c-plp .view-alshaya-product-list .view-header');
          selector = $('.c-content__region .total-result-count, .facet-all-count');
        }
        else {
          viewHeader = $('.c-plp .view-alshaya-product-list .view-header');
          selector = $('.c-content__region .total-result-count, .facet-all-count');
        }
        viewHeader.addClass('search-count');
        var searchCount = $('.c-content__region .search-count');
        // For mobile.
        if ($(window).width() < 768) {
          $('.block-page-title-block').addClass('mobile');
          searchCount.removeClass('tablet');
          if (viewHeader.length) {
            searchCount.remove();
            selector.html(viewHeader);
          }
          searchCount.addClass('only-mobile');
        }
        // For tablet and desktop.
        else {
          $('.block-page-title-block').removeClass('mobile');
          searchCount.removeClass('only-mobile');
          if (viewHeader.length) {
            searchCount.remove();
            selector.html(viewHeader);
          }
          searchCount.addClass('tablet');
        }
      }

      function processSoftLiniks(element) {
        try {
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
        catch (e) {
          // Do nothing.
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
            // Do nothing.
          }
          else if ($('body').hasClass('nodetype--acq_promotion')) {
            // Do Nothing for now.
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
            if ($(window).width() >= 768) {
              $('.page-wrapper, .header--wrapper, .c-pre-content, .plp-video, .c-breadcrumb, .region__banner-top, .branding__menu, .c-footer')
                .toggleClass('show-overlay');
              // Turn of transition for some regions.
              setTimeout(function () {
                $('.branding__menu').toggleClass('no-animate');
              }, 500);
            }
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

      // Keep the filters open that have checkboxes checked.
      $('div.block-facets-ajax').each(function () {
        $(this).find('input.facets-checkbox:checkbox:checked').each(function () {
          if (!$(this).closest('div.block-facets-ajax').hasClass('facet-active')) {
            $(this).closest('div.block-facets-ajax').addClass('facet-active');
            $(this).closest('ul').siblings('.facets-soft-limit-link').show();
            return false;
          }
        });

        // Swatch facets (color) are link not checkbox.
        $(this).find('ul li a').each(function () {
          // If link has 'is-active' class, means this color facet is
          // active/selected. Add active class at block level.
          if ($(this).hasClass('is-active')) {
            $(this).closest('div.block-facets-ajax').addClass('facet-active');
            return false;
          }
        });
      });

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

      // Close button to close the mobile filter view.
      $('span.filter-close', context).stop().on('click', function () {
        closeFilterView();
      });

      // Process facet checkbox softlimits on page load.
      $('.block-facet--checkbox, .block-facet--range-checkbox', context).each(function () {
        processSoftLiniks($(this));
      });

      // Process facet checkbox softlimits while rebuilding facets post AJAX.
      if ($(context).hasClass('block-facet--checkbox')) {
        processSoftLiniks($(context));
      }

      if ($(window).width() < 768 && $('.filter--mobile .clear-all').length > 0 && !$('#block-sizefacetblock').siblings().hasClass('shop-by-size-clear-container')) {
        // Show clear all button below to Bras size filter after facets block gets loaded.
        var sizeClear = $('.filter--mobile li.clear-all').clone();
        sizeClear.once('bind-events').insertAfter('#block-sizefacetblock').addClass('shop-by-size-clear-container');
      }
      else if ($('.filter--mobile .clear-all').length === 0 && $('#block-sizefacetblock').siblings().hasClass('shop-by-size-clear-container')) {
        // Remove if clear all button is clicked on the filter pane.
        $('.shop-by-size-clear-container').remove();
      }

      $('.c-facet__title.c-accordion__title').once().on('click', function () {
        if ($(this).hasClass('active')) {
          $(this).removeClass('active');
        }
        else {
          $(this).parent().siblings('.c-facet').find('.c-facet__title.active').removeClass('active');
          $(this).addClass('active');
        }
      });
    }
  };
})(jQuery);
