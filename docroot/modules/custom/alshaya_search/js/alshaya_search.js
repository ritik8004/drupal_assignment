/**
 * @file
 */

var alshayaSearchActiveFacetAfterAjaxTimer = null;

(function ($) {
  'use strict';

  Drupal.behaviors.alshayaSearch = {
    attach: function (context, settings) {
      // Hide the sort drop down and filters text, if no results.
      if ($('.view-id-search .view-empty').length !== 0) {
        $('#views-exposed-form-search-page .form-item-sort-bef-combine').hide();
        $('.c-sidebar-first__region .c-facet__blocks__wrapper .c-facet__label').remove();
        // Hide count item on no result.
        $('.search-count').hide();
      }
      // Do not allow search form submit on empty search text.
      $('form[data-bef-auto-submit-full-form]', context).submit(function (e) {
        var $keyword = $(this).find('input[name="keywords"]');
        if (typeof $keyword.val() == 'undefined' || $.trim($keyword.val()) === '') {
          e.preventDefault();
        }
      });
    }
  };

  // Ajax command to update search result header count.
  $.fn.alshayaSearchHeaderUpdate = function (data) {
    // If search page.
    if ($('.view-id-search').length !== 0) {
      // Update the header result count.
      var header_result = $('.view-id-search .view-header').html();
      $('.search-count').html(header_result);
    }
  };

  Drupal.behaviors.alshayaFacets = {
    attach: function (context, settings) {
      if ($('.block-facets-ajax').length === 0) {
        return;
      }

      $.fn.replaceFacets = function (data) {
        if (data.replaceWith === '') {
          $(data.selector).html('');
        }
        else {
          $(data.selector).replaceWith(data.replaceWith);
        }
      };

      var facetsDisplayTextbox = settings.alshaya_search_facets_display_textbox;
      if (facetsDisplayTextbox) {
        var facetPlugins = Object.keys(facetsDisplayTextbox);
        $('.block-facets-ajax').each(function () {
          var blockPluginId = $(this).attr('data-block-plugin-id');
          if ($.inArray(blockPluginId, facetPlugins !== -1) &&
            ($(this).find('li.facet-item').length >= facetsDisplayTextbox[blockPluginId]) &&
            ($(this).find('.facets-search-input').length === 0)) {
            // Prepend the text field before the checkboxes, if not exists.
            $(this).find('ul').prepend('<input type="text" placeholder="'
              + Drupal.t('Enter your filter name')
              + '" class="facets-search-input">').on('keyup', function () {
              var facetFilterKeyword = $(this).find('.facets-search-input').val().toLowerCase();
              if (facetFilterKeyword) {
                $(this).find('li').each(function () {
                  // Hide all facet links.
                  $(this).hide();
                  if ($(this).find('.facet-item__value').html().toLowerCase().search(facetFilterKeyword) >= 0) {
                    $(this).show();
                  }
                });
              }
              else {
                // Show all facet items.
                $(this).find('li:hidden').show();
                if (settings.facets.softLimit !== undefined) {
                  // If soft limit is rendered, show the link.
                  $(this).parent().find('.facets-soft-limit-link').show();
                  if (!$(this).parent().find('.facets-soft-limit-link').hasClass('open')) {
                    // Show only soft limit items, if facets were collapsed.
                    var facetName = $(this).attr('data-drupal-facet-id');
                    var zeroBasedLimit = settings.facets.softLimit[facetName] - 1;
                    $(this).find('li:gt(' + zeroBasedLimit + ')').hide();
                  }
                }
              }
            });
          }
        });
      }

      // Only execute if views is not empty.
      if ($('.views-infinite-scroll-content-wrapper').length !== 0) {
        // Change the title of facet when open.
        var priceCurrency = settings.alshaya_search_price_currency;
        var $finalPriceBlock = $('#block-skusskureferencefinalprice');
        var finalPriceBlockSearch = $('#block-finalprice');
        if (priceCurrency) {
          var initialTitle = $finalPriceBlock.find('h3').html();
          var initialTitleSearch = finalPriceBlockSearch.find('h3').html();
          $finalPriceBlock.find('h3').on('click', function () {
            if ($(this).hasClass('ui-state-active')) {
              $finalPriceBlock.find('h3').html(initialTitle + ' (' + priceCurrency + ')');
            }
            else {
              $finalPriceBlock.find('h3').html(initialTitle);
            }
          });

          finalPriceBlockSearch.find('h3').on('click', function () {
            if ($(this).hasClass('ui-state-active')) {
              finalPriceBlockSearch.find('h3').html(initialTitleSearch + ' (' + priceCurrency + ')');
            }
            else {
              finalPriceBlockSearch.find('h3').html(initialTitleSearch);
            }
          });
        }
        try {
          // Price facets to respect Soft Limit.
          var facetName = $finalPriceBlock.find('ul').attr('data-drupal-facet-id');
          var zeroBasedLimit = settings.facets.softLimit[facetName] - 1;
          $finalPriceBlock.find('li:gt(' + zeroBasedLimit + ')').hide();

          // Price facets to respect Soft Limit.
          var facetNameSearch = finalPriceBlockSearch.find('ul').attr('data-drupal-facet-id');
          var zeroBasedLimitSearch = settings.facets.softLimit[facetNameSearch] - 1;
          finalPriceBlockSearch.find('li:gt(' + zeroBasedLimitSearch + ')').hide();
        }
        catch (e) {
          // Do nothing.
        }
      }

      $('.ui-autocomplete').on("touchend",function (e) {
        if (navigator.userAgent.match(/(iPod|iPhone|iPad)/)) {
          e.stopPropagation();
          e.preventDefault();
          if (e.handled !== true) {
            // Taking keyword input and suggestion from e.target, which is more reliable than class.
            var target = $(e.target);
            // Target is either a or li, use jQuery to find span inside.
            var input = target.find('.autocomplete-suggestion-user-input').html()
              + target.find('.autocomplete-suggestion-suggestion-suffix').html();
            $('#edit-keywords').val(input);
            $('#views-exposed-form-search-page').submit();
          }
        }
      });

      // Append active-item class to L2 active items in facet category list on SRP.
      $('ul[data-drupal-facet-id="category"] > li > ul > li > a, ul[data-drupal-facet-id="promotion_category_facet"] > li > ul > li > a').each(function () {
        if ($(this).hasClass('is-active')) {
          $(this).parent('li').addClass('active-item');
        }
      });

      // Doing this for ajax complete as dom/element we require are not available earlier.
      $(document).ajaxComplete(function (event, xhr, settings) {
        Drupal.addLeafClassToPlpLeafItems();

        if ($(window).width() < 768) {
          // Finding the active facet and showing/hidding category facets accordingly.
          var alshayaPlpSearchActiveFacet = $('.current-active-facet').attr('data-block-plugin-id');
          var alshayaPlpSearchActiveCategoryFacet = $('.current-active-facet.block-facet-blockplp-category-facet, .current-active-facet.block-facet-blockcategory, .current-active-facet.block-facet-blockpromotion-category-facet');
          var alshayaPlpSearchCategoryFacet = $('ul[data-drupal-facet-id=category].js-facets-checkbox-links, ul[data-drupal-facet-id=plp_category_facet].js-facets-checkbox-links, ul[data-drupal-facet-id=promotion_category_facet].js-facets-checkbox-links ');
          var alshayaPlpSearchCategoryFacetTitle = $('.block-facet-blockplp-category-facet .c-accordion__title, .block-facet-blockcategory .c-accordion__title, .block-facet-blockpromotion-category-facet .c-accordion__title');

          if (alshayaPlpSearchActiveFacet) {
            if (alshayaPlpSearchActiveCategoryFacet.length > 0) {
              alshayaPlpSearchCategoryFacet.show();
              alshayaPlpSearchCategoryFacetTitle.removeClass('ui-state-active');
            }
            else {
              alshayaPlpSearchCategoryFacet.hide();
              alshayaPlpSearchCategoryFacetTitle.addClass('ui-state-active');
            }
          }
        }
      });

      // Add Class to leaf items on page load.
      Drupal.addLeafClassToPlpLeafItems();

      // Add checkboxes here to ensure we have it before our dependent code.
      // @see docroot/modules/contrib/facets/js/checkbox-widget.js
      Drupal.facets.makeCheckboxes();

      // Hide other category filter options when one of the L1 items is selected.
      Drupal.alshayaSearchProcessCategoryFacets();
    }
  };

  Drupal.addLeafClassToPlpLeafItems = function () {
    // On PLP page, we assuming that if there is no expanded and collapsed class available,
    // it means we at the leaf nodes level and thus we adding class to show for the checkboxes.
    if ($('ul[data-drupal-facet-id="plp_category_facet"] .facet-item--collapsed').length === 0
      && $('ul[data-drupal-facet-id="plp_category_facet"] .facet-item--expanded').length === 0) {
      $('ul[data-drupal-facet-id="plp_category_facet"] li').each(function () {
        $(this).addClass('leaf-li');
      });
    }
  };

  Drupal.alshayaSearchProcessCategoryFacets = function () {
    if ($('ul[data-drupal-facet-id="category"], ul[data-drupal-facet-id="promotion_category_facet"]').find('input[checked="checked"]').length > 0) {
      $('ul[data-drupal-facet-id="category"], ul[data-drupal-facet-id="promotion_category_facet"]').children('li').each(function () {
        if ($(this).hasClass('facet-item--expanded') ||
          ($(this).children('input[checked="checked"]').length > 0)) {
          return;
        }
        else {
          $(this).hide();
        }
      });
    }
  };

  Drupal.behaviors.searchSlider = {
    attach: function (context, settings) {
      function applyRtl(ocObject, options) {
        if (isRTL() && $(window).width() > 1025) {
          ocObject.attr('dir', 'rtl');
          ocObject.slick(
            $.extend({}, options, {rtl: true})
          );
          if (context !== document) {
            ocObject.slick('resize');
          }
        }
        else {
          ocObject.slick(options);
          if (context !== document) {
            ocObject.slick('resize');
          }
        }
      }

      if (settings.plp_slider) {
        var slickOptions = {
          slidesToShow: settings.plp_slider.item,
          slidesToScroll: 1,
          vertical: false,
          arrows: true,
          focusOnSelect: false,
          infinite: false,
          touchThreshold: 1000,
        };

        // Convert the list to slider.
        $('.search-lightSlider', context).once('alshayaSearchSlider').each(function () {
          // Create the slider.
          applyRtl($(this), slickOptions);

          // Handle click events in hover slider arrows without triggering click to PDP.
          $(this).find('.slick-arrow').on('click', function (e) {
            if (e.preventDefault) {
              e.preventDefault();
            }
            else {
              e.returnValue = false;
            }

            if (!$(this).hasClass('slick-disabled')) {
              if ($(this).attr('class') === 'slick-prev') {
                $(this).parent().slick('slickPrev');
              }
              else {
                $(this).parent().slick('slickNext');
              }
            }
            return false;
          });
        });

        // Change the image on Mouse hover.
        // Adding a delay here to avoid flicker during scroll in between two slides.
        // This also helps in smoothing the mouseout behaviour.
        var delay = 500;
        var setTimeoutConst;
        $('.alshaya_search_slider .slick-slide', context).once('alshayaSearchSlider').hover(
          function () {
            // Clear timer when we enter a new thumbnail.
            clearTimeout(setTimeoutConst);
            $(this)
              .closest('.alshaya_search_gallery')
              .find('.alshaya_search_mainimage img')
              .attr('src', $(this).find('img').attr('rel'));
          },
          function () {
            // Store this as after delay the mouse is not on element, so this changes.
            var el = $(this);
            // Delay the resetting of main image post hover out.
            setTimeoutConst = setTimeout(function () {
              el.parents('.alshaya_search_gallery').find('.alshaya_search_mainimage img').attr('src',
                el.parent().find('li:first-child').find('img').attr('rel')
              );
            }, delay);
          }
        );

        // Preload slider images.
        if ($(window).width() > 1024) {
          // Iterate over each product tile.
          $('.c-products__item').each(function () {
            var slider = $(this).find('.alshaya_search_slider');
            // Iterate over each slider thumbnail.
            slider.find('.slick-slide').each(function () {
              var imgURL = $(this).children('img').attr('rel');
              // Preload image.
              var img = new Image();
              img.src = imgURL;
            });
          });
        }

        $.fn.alshayaAttachSearchSlider = function () {
          Drupal.attachBehaviors(context);
        };
      }
    }
  };

  Drupal.behaviors.convertL2ToAccordion = {
    attach: function (context, settings) {
      $('[data-drupal-facet-id="category"] .facet-item, [data-drupal-facet-id="plp_category_facet"] .facet-item, [data-drupal-facet-id="promotion_category_facet"] .facet-item').each(function () {
        if ($(this).children('a').length > 0) {
          // Extract query string from the relative url string.
          var facet_url_query_string = ($(this).children('a').attr('href')).match(/(\?.*)/);
          if (facet_url_query_string) {
            var urlParams = new URLSearchParams(facet_url_query_string[1]);
            // Process items with no_url_l2 parameter.
            if ((urlParams.has('no_url_l2')) &&
              (!$(this).hasClass('l2-processed'))) {
              // Stop click on a tag from directing users to the url.
              $(this).children('a').off('click').click(function (e) {
                return false;
              });

              // Remove checkbox for the selected L2 items.
              $(this).children('input').remove();

              // Attach a click listener to the L2 items to make it act like
              // accordion.
              $(this).off('click').click(function (e) {
                $(this).children('ul').slideToggle();
                $(this).addClass('l2-processed');
                e.stopPropagation();
              });

              // Only keep the current selection expanded.
              if (!urlParams.has('current_facet')) {
                $(this).children('ul').once('js-event').slideToggle();
              }
            }
          }
        }
      });
    }
  };

  /**
   * Helper function to convert full-screen loader to throbber for infinite scroll.
   */
  Drupal.changeProgressBarToThrobber = function (context) {
    Drupal.ajax.instances.forEach(function (ajax_instance, key) {
      if ((ajax_instance) && (ajax_instance.hasOwnProperty('element')) &&
        ($(ajax_instance.element, context).hasClass('c-products-list') ||
        ($(ajax_instance.element, context).parents('ul[data-drupal-views-infinite-scroll-pager="automatic"]').length > 0))) {
        Drupal.ajax.instances[key].progress.type = 'throbber';
      }
    });
  };

  /**
   * Drupal behaviors to update progressBars.
   */
  Drupal.behaviors.processProgressBarsForAjax = {
    attach: function (context, settings) {
      // Avoid auto scroll on the listing from the state that browser remembers.
      // This at times leads to trigger of infinite scroll before updating the
      // progress bar.
      history.scrollRestoration = 'manual';

      // Update only when all DOM elements are loaded & AJAX is attached to them.
      $(window).on('load', function () {
        Drupal.changeProgressBarToThrobber(context);
      });

      // Update on Ajax complete to take care of AJAX instance updates.
      if (context !== document) {
        Drupal.changeProgressBarToThrobber(context);
      }
    }
  };
})(jQuery);
