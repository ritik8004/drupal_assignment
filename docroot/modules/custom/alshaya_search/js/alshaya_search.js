var alshayaSearchActiveFacet = null;
var alshayaSearchShowMoreOpen = 0;
var alshayaSearchActiveFacetTimer = null;
var alshayaSearchActiveFacetAfterAjaxTimer = null;

(function ($) {
  'use strict';

  Drupal.behaviors.alshayaSearch = {
    attach: function (context, settings) {
      // Hide the sort drop down and filters text, if no results.
      if ($('.view-id-search .view-empty').length !== 0) {
        $('#views-exposed-form-search-page .form-item-sort-bef-combine').hide();
        $('.c-sidebar-first__region .c-facet__blocks__wrapper .c-facet__label').remove();
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

  Drupal.alshayaSearchActiveFacetObserver = function () {
    alshayaSearchActiveFacet = null;
    alshayaSearchShowMoreOpen = 0;

    if ($('.facet-active').length) {
      alshayaSearchActiveFacet = $('.facet-active').attr('data-block-plugin-id');
      alshayaSearchShowMoreOpen = $('.facet-active .facets-soft-limit-link.open').length;
    }

    Drupal.alshayaSearchBindObserverEvents();
  };

  Drupal.alshayaSearchActiveFacetResetAfterAjax = function () {
    // We apply soft-limit js again after ajax calls here.
    // Soft limit is feature provided by facets module but it
    // doesn't support ajax, we add code here to handle that.
    if (alshayaSearchActiveFacet) {
      if (typeof drupalSettings.facets.softLimit !== 'undefined'
        && typeof drupalSettings.facets.softLimit[alshayaSearchActiveFacet] !== 'undefined') {

        var limit = drupalSettings.facets.softLimit[alshayaSearchActiveFacet];
        Drupal.facets.applySoftLimit(alshayaSearchActiveFacet, limit);
      }

      var facetBlock = $('[data-block-plugin-id="' + alshayaSearchActiveFacet + '"]:visible');
      facetBlock.addClass('facet-active');
      facetBlock.find('.c-accordion__title').addClass('ui-state-active');
      facetBlock.find('.facets-soft-limit-link').css('display', 'inline-block');

      if (alshayaSearchShowMoreOpen) {
        facetBlock.find('li').show();
        facetBlock.find('.facets-soft-limit-link').addClass('open').text(Drupal.t('Show less'));
      }
    }

    Drupal.alshayaSearchBindObserverEvents();
  };

  Drupal.alshayaSearchBindObserverEvents = function () {
    $('.c-facet__blocks').on('click', function () {
      if (alshayaSearchActiveFacetTimer) {
        clearTimeout(alshayaSearchActiveFacetTimer);
        alshayaSearchActiveFacetTimer = null;
      }

      alshayaSearchActiveFacetTimer = setTimeout(Drupal.alshayaSearchActiveFacetObserver, 100);
    });
  };

  Drupal.behaviors.alshayaFacets = {
    attach: function (context, settings) {
      Drupal.alshayaSearchBindObserverEvents();

      $.fn.replaceFacets = function(data) {
        if (data.replaceWith === '') {
          $(data.selector).html('');
        }
        else {
          $(data.selector).replaceWith(data.replaceWith);

          if (alshayaSearchActiveFacetAfterAjaxTimer) {
            clearTimeout(alshayaSearchActiveFacetAfterAjaxTimer);
            alshayaSearchActiveFacetAfterAjaxTimer = null;
          }

          alshayaSearchActiveFacetAfterAjaxTimer = setTimeout(Drupal.alshayaSearchActiveFacetResetAfterAjax, 100);
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
          $finalPriceBlock.find('h3').on('click', function() {
            if ($(this).hasClass('ui-state-active')) {
              $finalPriceBlock.find('h3').html(initialTitle + ' (' + priceCurrency + ')');
            }
            else {
              $finalPriceBlock.find('h3').html(initialTitle);
            }
          });

          finalPriceBlockSearch.find('h3').on('click', function() {
            if ($(this).hasClass('ui-state-active')) {
              finalPriceBlockSearch.find('h3').html(initialTitleSearch + ' (' + priceCurrency + ')');
            }
            else {
              finalPriceBlockSearch.find('h3').html(initialTitleSearch);
            }
          });
        }

        // Price facets to respect Soft Limit.
        var facetName = $finalPriceBlock.find('ul').attr('data-drupal-facet-id');
        var zeroBasedLimit = settings.facets.softLimit[facetName] - 1;
        $finalPriceBlock.find('li:gt(' + zeroBasedLimit + ')').hide();

        // Price facets to respect Soft Limit.
        var facetNameSearch = finalPriceBlockSearch.find('ul').attr('data-drupal-facet-id');
        var zeroBasedLimitSearch = settings.facets.softLimit[facetNameSearch] - 1;
        finalPriceBlockSearch.find('li:gt(' + zeroBasedLimitSearch + ')').hide();
      }

      $('.ui-autocomplete').on("touchend",function(e) {
        if (navigator.userAgent.match(/(iPod|iPhone|iPad)/)) {
          e.stopPropagation();
          e.preventDefault();
          if (e.handled !== true) {
            // Taking keyword input and suggestion from e.target, which is more reliable than class.
            var target = $(e.target);
            // target is either a or li, use jQuery to find span inside.
            var input = target.find('.autocomplete-suggestion-user-input').html()
              + target.find('.autocomplete-suggestion-suggestion-suffix').html();
            $('#edit-keywords').val(input);
            $('#views-exposed-form-search-page').submit();
          }
        }
      });

      // Hide other category filter options when one of the L1 items is selected.
      if ((jQuery('ul[data-drupal-facet-id="category"]').children('li.facet-item--expanded')).length > 0) {
        jQuery('[data-drupal-facet-id="category"]').children('li:not(.facet-item--expanded)').hide();
      }

      // Hide other category filter options when one of the L1 items is
      // selected for the PLP category facet.
      if ((jQuery('ul[data-drupal-facet-id="plp_category_facet"]').children('li.facet-item--expanded')).length > 0) {
        jQuery('[data-drupal-facet-id="plp_category_facet"]').children('li:not(.facet-item--expanded)').hide();
      }

      // Doing this for ajax complete as dom/element we require are not available earlier.
      $(document).ajaxComplete(function(event, xhr, settings) {
        // On PLP page, we assuming that if there is no expanded and collapsed class available,
        // it means we at the leaf nodes level and thus we adding class to show for the checkboxes.
        if (jQuery('ul[data-drupal-facet-id="plp_category_facet"] .facet-item--collapsed').length === 0
        && jQuery('ul[data-drupal-facet-id="plp_category_facet"] .facet-item--expanded').length === 0) {
          jQuery('ul[data-drupal-facet-id="plp_category_facet"] li').each(function(){
            $(this).addClass('leaf-li');
          });
        }
      });

    }
  };

  Drupal.behaviors.searchSlider = {
    attach: function (context, settings) {
      // Convert the list to slider.
      $('.search-lightSlider', context).once('alshayaSearchSlider').each(function () {
        var gallery = $(this);
        if (isRTL()) {
          $(this, context).lightSlider({
            vertical: false,
            item: 4,
            rtl: true,
            slideMargin: 5,
            onSliderLoad: function() {
              gallery.css('height', '73px');
            }
          });
        }
        else {
          $(this, context).lightSlider({
            vertical: false,
            item: 4,
            slideMargin: 5,
            onSliderLoad: function() {
              gallery.css('height', '73px');
            }
          });
        }
      });

      // Change the image on Mouse hover.
      $('.alshaya_search_slider img', context).hover(
        function () {
          $(this)
            .closest('.alshaya_search_gallery')
            .find('.alshaya_search_mainimage img')
            .attr('src', $(this).attr('rel'));
        },
        function () {
          $(this)
            .closest('.alshaya_search_gallery')
            .find('.alshaya_search_mainimage img')
            .attr('src', $(this)
              .parent()
              .parent()
              .find('li:first-child img')
              .attr('rel'));
        }
      );

      // Preload slider images.
      if ($(window).width() > 1024) {
        // Iterate over each product tile.
        $('.c-products__item').each(function () {
          var slider = $(this).find('.alshaya_search_slider');
          // Iterate over each slider thumbnail.
          slider.find('.lslide').each(function () {
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
  };

  Drupal.behaviors.convertL2ToAccordion = {
    attach: function(context, settings) {
      $('[data-drupal-facet-id="category"] .facet-item').each(function() {
        if ($(this).children('a').length > 0) {
          // Extract query string from the relative url string.
          var facet_url_query_string = ($(this).children('a').attr('href')).match(/(\?.*)/);
          if (facet_url_query_string) {
            var urlParams = new URLSearchParams(facet_url_query_string[1]);
            // Process items with no_url_l2 parameter.
            if ((urlParams.has('no_url_l2')) &&
              (!$(this).hasClass('l2-processed'))) {
              // Stop click on a tag from directing users to the url.
              $(this).children('a').off('click').click(function(e) {
                return false;
              });

              // Remove checkbox for the selected L2 items.
              $(this).children('input').remove();

              // Attach a click listener to the L2 items to make it act like
              // accordion.
              $(this).off('click').click(function(e) {
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
  }
})(jQuery);
