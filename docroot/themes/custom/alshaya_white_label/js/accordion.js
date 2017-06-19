/**
 * @file
 * Sliders.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.accordion = {
    attach: function (context, settings) {

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
        if (countFilters === 0 && $.trim($(mobileFilterBarSelector)
            .html()).length === 0) {
          $(mobileFilterBarSelector)
            .once()
            .append('<h3 class="applied-filter-count c-accordion__title">' + Drupal.t('applied filters')
              + ' (' + countFilters + ')</h3>');
          $(mobileFilterBarSelector).addClass('empty');
        }
        else {
          if (countFilters > 0) {
            // Removing the element before adding again.
            $(mobileFilterBarSelector + ' > h3').remove();
            // We need to minus one count as the facets also include clear all link.
            countFilters = countFilters - 1;
            // If there are filters applied, we need to show the count next to the label.
            $('<h3 class="applied-filter-count c-accordion__title">' + Drupal.t('applied filters')
              + '(' + countFilters + ')</h3>')
              .insertBefore(mobileFilterBarSelector + ' ul');
          }
        }
      }

      function mobileFilterMenu() {
        // Mobile filter block selector.
        var mobileFilterBarSelector = getFilterBarSelector();
        // The original filter block.
        var filterBarSelector = null;
        if ($('body').hasClass('path--search')) {
          filterBarSelector = '.region__content > .block-facets-summary-blockfilter-bar';
        }
        else {
          filterBarSelector = '.region__content > .block-facets-summary-blockfilter-bar-plp';
        }

        if ($(window).width() < 768) {
          // Facet Block selector.
          var facetBlocks = $('.c-facet__blocks__wrapper .c-facet__blocks');

          // Check if we have filter label.
          var filterLabel = facetBlocks.find('.filter-menu-label');
          if (filterLabel.length) {
            // This is an ajax call.
          }
          else {
            // If we dont have one, create it, this is first time load.
            $('<div class="filter-menu-label"><span class="label">filter</span><li class="clear-all-fake"><span>clear all</span></li></div>')
              .insertBefore('.region__content .c-facet__blocks .region__sidebar-first ');
          }

          if ($(mobileFilterBarSelector).length) {
            placeFilterCount();
          }
          else {
            // Clone the filter block from region content.
            var blockFilterBar = $(filterBarSelector).clone();

            // Place the cloned bar before other facets in the region content's sidebar first.
            $(blockFilterBar)
              .insertBefore('.region__content .c-facet__blocks .region__sidebar-first div:first-child');

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

      function moveContextualLink(parent, body) {
        if (typeof body === 'undefined') {
          body = '.c-accordion__title';
        }
        $(parent).each(function () {
          var contextualLink = $(this).find(body).next();
          $(this).append(contextualLink);
        });
      }

      /**
       * Place the search count from view header in different locations based on resolution.
       */
      function placeSearchCount() {
        var viewHeader = null;
        var searchCount = null;
        var selector = null;
        var filterSelector = null;
        if ($('body').hasClass('path--search')) {
          viewHeader = $('.c-search .view-search .view-header');
          selector = '.c-content__region .block-views-exposed-filter-blocksearch-page';
          filterSelector = '.c-content__region .region__content .block-facets-summary-blockfilter-bar';
        }
        else {
          viewHeader = $('.c-plp .view-alshaya-product-list .view-header');
          selector = '.c-content__region .block-views-exposed-filter-blockalshaya-product-list-block-1';
          filterSelector = '.c-content__region .region__content .block-facets-summary-blockfilter-bar-plp';
        }
        viewHeader.addClass('search-count');
        searchCount = $('.c-content__region .search-count');
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
            viewHeader.insertBefore(filterSelector);
          }
          searchCount.addClass('tablet');
        }
      }

      /**
       * Toggles the footer accordions.
       */

      if ($('.c-footer-is-accordion').length) {
        var accordionHead = $('.c-footer-is-accordion .is-accordion');
        var accordionBody = $(accordionHead).nextAll();

        $(accordionBody).addClass('accordion--body');
        $(accordionHead).once().click(function () {
          var $ub = $(this).nextAll().stop(true, true).slideToggle();
          accordionBody.not($ub).slideUp();
          $ub.parent().toggleClass('open--accordion');
          accordionBody.not($ub).parent().removeClass('open--accordion');
        });
      }

      if (context === document) {
        // Toggle for Product description.
        $('.read-more-description-link').on('click', function () {
          $('.c-pdp .description-wrapper').toggle('slow');
          if ($(window).width() < 768) {
            $('.c-pdp .short-description-wrapper').toggle('slow');
            if ($('.c-pdp .description-wrapper .show-less-link').length < 1) {
              $('.c-pdp .description-wrapper .field__content')
                .append('<div class="show-less-link">' + Drupal.t('Show less') + '</div>');
            }
          }
        });

        $('.close').on('click', function () {
          $('.c-pdp .description-wrapper').toggle('slow');
        });

        $('.c-pdp .description-wrapper .field__content')
          .on('click', '.show-less-link', function () {
            if ($(window).width() < 768) {
              $('.c-pdp .short-description-wrapper').toggle('slow');
              $('.c-pdp .description-wrapper').toggle('slow');
            }
          });

        moveContextualLink('.c-accordion');

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
          else {
            mainBlock.before(facetBlockWrapper);
            var plpFilter = $('.c-plp #views-exposed-form-alshaya-product-list-block-1');
            plpFilter.wrapAll('<div class="view-filters is-filter">');
            $('.is-filter').wrapAll('<div class="filter--mobile clearfix">');
            $('.region__content .c-facet__blocks__wrapper')
              .insertBefore('.view-filters.is-filter');
          }

          facetLabel.click(function () {
            $('.page-wrapper, .header--wrapper, .c-pre-content, .c-breadcrumb, .branding__menu')
              .toggleClass('show-overlay');
            facetLabel.toggleClass('is-active');
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

        // Accordion for delivery option section on PDP.
        $('.delivery-options-wrapper').once('bind-event').each(function () {
          $('.c-accordion-delivery-options', $(this)).accordion({
            heightStyle: 'content',
            collapsible: true,
            active: false
          });
        });

        // Add class to promotional banner view block if it is not empty.
        if (!$('.view-plp-promotional-banner .field-content').is(':empty')) {
          $('.block-views-blockplp-promotional-banner-block-1')
            .addClass('promo-banner');
          $('.block-views-blockplp-promotional-banner-block-1')
            .siblings('.block-views-exposed-filter-blocksearch-page')
            .addClass('promo-banner');
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
        $('.page-wrapper, .header--wrapper, .c-pre-content, .c-breadcrumb, .branding__menu')
          .toggleClass('show-overlay');
        $('.c-facet__blocks__wrapper .c-facet__label').toggleClass('is-active');
        $('.c-facet__blocks__wrapper .c-facet__blocks').toggle();
      });

      $('.c-facet__blocks')
        .find('.c-accordion__title')
        .off()
        .on('click', function (e) {
          Drupal.alshayaAccordion(this);
        });

      // Click event for fake clear all link on mobile filter.
      var mobileFilterBarSelector = getFilterBarSelector();
      $('.clear-all-fake', context).stop().on('click', function () {
        $(mobileFilterBarSelector + ' .clear-all').trigger('click');
        return false;
      });

      /**
       * Toggles the Expand Order Accordions.
       */

      if ($('.recent__orders--list .order-summary-row').length) {
        var parentOrder = $('.recent__orders--list .order-summary-row');
        var listOrder = $('.recent__orders--list .order-item-row');

        $(listOrder).hide();

        $(parentOrder).click(function () {
          var $ub = $(this).nextAll().stop(true, true).slideToggle();
          listOrder.not($ub).hide();
          $ub.parent().toggleClass('open--accordion');
          listOrder.not($ub).parent().removeClass('open--accordion');
        });
      }

      /**
       * Toggles the Tabs.
       */

      if ($('.checkout .multistep-checkout').length) {
        $('.tab-home-delivery, .tab-new-customer')
          .addClass('active--tab--head');
        $('#edit-guest-delivery-home, #edit-member-delivery-home, #edit-checkout-guest')
          .addClass('active--tab--content');

        $('.tab').click(function () {
          $('.multistep-checkout .tab').removeClass('active--tab--head');
          $('.multistep-checkout fieldset').removeClass('active--tab--content');

          if ($(this).hasClass('tab-home-delivery')) {
            $('.tab-home-delivery').addClass('active--tab--head');
            $('#edit-guest-delivery-home, #edit-member-delivery-home')
              .addClass('active--tab--content');
          }
          else {
            if ($(this).hasClass('tab-click-collect')) {
              $('.tab-click-collect').addClass('active--tab--head');
              $('#edit-guest-delivery-collect, #edit-member-delivery-collect')
                .addClass('active--tab--content');
            }
            else {
              if ($(this).hasClass('tab-new-customer')) {
                $('.tab-new-customer').addClass('active--tab--head');
                $('#edit-checkout-guest').addClass('active--tab--content');
              }
              else {
                if ($(this).hasClass('tab-returning-customer')) {
                  $('.tab-returning-customer').addClass('active--tab--head');
                  $('#edit-checkout-login').addClass('active--tab--content');
                }
              }
            }
          }
        });

        $('.multistep-checkout legend').click(function () {
          $(this).next('.fieldset-wrapper').slideToggle();
        });
      }

      /**
       * Toggles the Search on Order list.
       */

      if ($('.alshaya-acm-customer-order-list-search').length) {
        $('.alshaya-acm-customer-order-list-search label')
          .on('click', function () {
            $('.alshaya-acm-customer-order-list-search')
              .toggleClass('active--search');
          });
      }

      /**
       * Toggles the Order confirmation table.
       */
      if ($('.multistep-checkout .user__order--detail').length) {
        $('.collapse-row').slideUp();
        $('.product--count').on('click', function () {
          $('#edit-confirmation-continue-shopping')
            .toggleClass('expanded-table');
          $(this).toggleClass('expanded-row');
          $(this).nextAll('.collapse-row').slideToggle();
        });
      }

      // Poll the DOM to check if the show more/less link is available, before placing it inside the ul.
      var i = setInterval(function () {
        if ($('.c-plp-only .block-facet--checkbox a.facets-soft-limit-link').length) {
          clearInterval(i);
          $('.block-facet--checkbox').each(function () {
            var softLink = $(this).find('a.facets-soft-limit-link');
            var blockPlugin = $(this).attr('data-block-plugin-id');
            var facet_id = blockPlugin.replace('facet_block:', '');
            var softLimitSettings = settings.facets.softLimit;
            var softItemsLimit = softLimitSettings[facet_id] - 1;
            $(this).find('ul li:gt(' + softItemsLimit + ')').hide();
            softLink.insertAfter($(this).find('ul li:last-child'));
          });
        }
      }, 100);

      var j = setInterval(function () {
        if ($('.c-plp-only .region__content .block-facet--checkbox a.facets-soft-limit-link').length) {
          clearInterval(j);
          $('.region__content .block-facet--checkbox').each(function () {
            var softLink = $(this).find('a.facets-soft-limit-link');
            softLink.addClass('processed');
            softLink.insertAfter($(this).find('ul li:last-child'));
          });
        }
      }, 100);
    }
  };

  Drupal.alshayaAccordion = function (element) {
    $(element).siblings().slideToggle('slow');
    $(element).toggleClass('ui-state-active');
  };

})(jQuery, Drupal);
