/**
 * @file
 * Sliders.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.accordion = {
    attach: function (context, settings) {
      function mobileFilterMenu() {
        if ($(window).width() < 768) {
          var facetBlocks = $('.c-facet__blocks__wrapper .c-facet__blocks');
          var filterLabel = facetBlocks.find('.filter-menu-label');
          if (filterLabel.length) {
            // Already exists, dont create again.
          }
          else {
            $('<div class="filter-menu-label"><span class="label">filter</span></div>').insertBefore('.region__content .c-facet__blocks .region__sidebar-first ');
            var blockFilterBar = $('#block-filterbar').clone();
            $(blockFilterBar).insertBefore('.region__content .c-facet__blocks .region__sidebar-first div:first-child');
            $('.region__content .region__sidebar-first #block-filterbar').addClass('mobile-filter-bar c-accordion');
            $('.mobile-filter-bar ul li.clear-all').insertAfter('.filter-menu-label .label');
            var countFilters = $('.mobile-filter-bar ul li').length;
            if (countFilters === 0 && $.trim($('.mobile-filter-bar').html()).length === 0) {
              $('.mobile-filter-bar').append('<h3 class="applied-filter-count c-accordion__title">' + Drupal.t('applied filters')
                + ' (' + countFilters + ')</h3>');
              $('.mobile-filter-bar').addClass('empty');
            }
            else {
              $('<h3 class="applied-filter-count c-accordion__title">' + Drupal.t('applied filters')
                + '(' + countFilters + ')</h3>').insertBefore('.mobile-filter-bar ul');
            }
          }
          // Toggle the filter menu when click on the label.
          $('.filter-menu-label .label').on('click', function () {
            $('.page-wrapper, .header--wrapper, .c-pre-content').toggleClass('show-overlay');
            $('.c-facet__blocks__wrapper .c-facet__label').toggleClass('is-active');
            $('.c-facet__blocks__wrapper .c-facet__blocks').toggle();
          });

          $('.region__content > #block-filterbar').hide();
        }

        else {
          $('.region__content > #block-filterbar').show();
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
       * Custom accordion for facet blocks so that multiple panes can be opened.
       *
       * @param {Object} element
       * The header, which will be clicked.
       */
      function alshayaAccordion(element) {
        $(element).siblings().slideToggle('slow');
        $(element).toggleClass('ui-state-active');
      }

      /**
       * Place the search count from view header in different locations based on resolution.
       */
      function placeSearchCount() {
        var viewHeader = $('.c-search .view-search .view-header');
        viewHeader.addClass('search-count');
        var searchCount = $('.c-content__region .search-count');
        // For mobile.
        if ($(window).width() < 768) {
          $('.block-page-title-block').addClass('mobile');
          searchCount.removeClass('tablet');
          if (viewHeader.length) {
            if (!$('.c-content__region .search-count.only-mobile').length) {
              viewHeader.insertBefore('.c-content__region .block-views-exposed-filter-blocksearch-page');

            }
          }
          else {
            if (!$('.c-content__region .search-count.only-mobile').length) {
              searchCount.insertBefore('.c-content__region .block-views-exposed-filter-blocksearch-page');
            }
          }
          searchCount.addClass('only-mobile');
        }
        // For tablet and desktop.
        else {
          $('.block-page-title-block').removeClass('mobile');
          searchCount.removeClass('only-mobile');
          if (viewHeader.length) {
            viewHeader.insertBefore('.c-content__region .region__content > #block-filterbar');
          }
          else {
            if (!$('.c-content__region .search-count.tablet').length) {
              searchCount.insertBefore('.c-content__region .region__content > #block-filterbar');
            }
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
        $(accordionHead).click(function () {
          var $ub = $(this).nextAll().stop(true, true).slideToggle();
          accordionBody.not($ub).slideUp();
          $ub.parent().toggleClass('open--accordion');
          accordionBody.not($ub).parent().removeClass('open--accordion');
        });
      }

      if (context === document) {
        moveContextualLink('.c-accordion');

        if ($('.c-facet__blocks__wrapper').length) {
          var facetBlockWrapper = $('.c-facet__blocks__wrapper').clone(true, true);
          var mainBlock = $('.block-system-main-block');
          var facetLabel = facetBlockWrapper.find('.c-facet__label');
          var facetBlock = facetBlockWrapper.find('.c-facet__blocks');

          facetBlockWrapper.addClass('c-facet__blocks__wrapper--mobile').addClass('is-filter');
          if ($('body').hasClass('path--search')) {
            mainBlock.before(facetBlockWrapper);
            var searchFilter = $('.c-search #views-exposed-form-search-page');
            searchFilter.wrapAll('<div class="view-filters is-filter">');
            $('.is-filter').wrapAll('<div class="filter--mobile clearfix">');
            $('.block-views-exposed-filter-blocksearch-page .c-facet__blocks__wrapper').insertBefore('.view-filters.is-filter');
          }
          else {
            mainBlock.after(facetBlockWrapper);
            var viewFilter = $('.c-products-list .view-filters');
            viewFilter.addClass('is-filter');
            $('.is-filter').wrapAll('<div class="filter--mobile clearfix">');
          }
          facetLabel.click(function () {
            $('.page-wrapper, .header--wrapper, .c-pre-content').toggleClass('show-overlay');
            facetLabel.toggleClass('is-active');
            facetBlock.toggle();
          });
        }

        // Hiding the filter border if there are no filters.
        var checkFilter = $.trim($('.c-search .region__content .block-facets-summary-blockfilter-bar').html());
        if (checkFilter.length) {
          $('.c-search .region__content .block-facets-summary-blockfilter-bar').css('border-bottom-width', '1px');
        }
        else {
          $('.c-search .region__content .block-facets-summary-blockfilter-bar').css('border-bottom-width', '0');
        }

        // Add class to clear all link and move it to the last.
        $('#block-filterbar ul li:first-child').addClass('clear-all');
        $('#block-filterbar ul li.clear-all').insertAfter('#block-filterbar ul li:last-child');

        // Clone the filter bar and add it to the filter menu on mobile.
        // Show mobile slider only on mobile resolution.
        mobileFilterMenu();
        placeSearchCount();
        $(window).on('resize', function (e) {
          mobileFilterMenu();
          placeSearchCount();
        });

        $('.c-facet__blocks .c-accordion__title').off().on('click', function (e) {
          alshayaAccordion(this);
        });

        // Accordion for delivery option section on PDP.
        $('.c-accordion-delivery-options').accordion({
          heightStyle: 'content',
          collapsible: true,
          active: false
        });

        // Toggle for Product description.
        var descwrapper = $('.c-pdp .description-wrapper');
        descwrapper.hide();
        $('.c-pdp .short-description-wrapper .read-more-description-link').on('click', function () {
          descwrapper.slideToggle();
        });

        $('.c-pdp .description-wrapper .close').on('click', function () {
          descwrapper.slideToggle();
        });

        // Add class to promotional banner view block if it is not empty.
        if (!$('.view-plp-promotional-banner .field-content').is(':empty')) {
          $('.block-views-blockplp-promotional-banner-block-1').addClass('promo-banner');
        }
      }

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

      if ($('.c-delivery-checkout .multistep-checkout').length) {
        $('.tab-home-delivery').addClass('active--tab--head');
        $('#edit-guest-delivery-home, #edit-member-delivery-home').addClass('active--tab--content');

        $('.tab').click(function () {
          $('.multistep-checkout .tab').removeClass('active--tab--head');
          $('.multistep-checkout fieldset').removeClass('active--tab--content');

          if ($(this).hasClass('tab-home-delivery')) {
            $('.tab-home-delivery').addClass('active--tab--head');
            $('#edit-guest-delivery-home, #edit-member-delivery-home').addClass('active--tab--content');
          }
          else if ($(this).hasClass('tab-click-collect')) {
            $('.tab-click-collect').addClass('active--tab--head');
            $('#edit-guest-delivery-collect, #edit-member-delivery-collect').addClass('active--tab--content');
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
        $('.alshaya-acm-customer-order-list-search label').on('click', function () {
          $('.alshaya-acm-customer-order-list-search').toggleClass('active--search');
        });
      }

      /**
      * Toggles the Order confirmation table.
      */

      if ($('.multistep-checkout .user__order--detail').length) {
        $('.collapse-row').slideUp();
        $('.product--count').on('click', function () {
          $('#edit-confirmation-continue-shopping').toggleClass('expanded-table');
          $(this).toggleClass('expanded-row');
          $(this).nextAll('.collapse-row').slideToggle();
        });
      }

      // Poll the DOM to check if the show more/less link is avaialble, before placing it inside the ul.
      var i = setInterval(function () {
        if ($('.c-plp-only aside .block-facet--checkbox a.facets-soft-limit-link').length) {
          clearInterval(i);
          $('aside .block-facet--checkbox').each(function () {
            var softLink = $(this).find('a.facets-soft-limit-link');
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

})(jQuery, Drupal);
