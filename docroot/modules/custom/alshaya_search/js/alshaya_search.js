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
    if (alshayaSearchActiveFacet) {
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
        var $finalPriceBlock = $('#block-finalprice');
        if (priceCurrency) {
          var initialTitle = $finalPriceBlock.find('h3').html();
          $finalPriceBlock.find('h3').on('click', function() {
            if ($(this).hasClass('ui-state-active')) {
              $finalPriceBlock.find('h3').html(initialTitle + ' (' + priceCurrency + ')');
            }
            else {
              $finalPriceBlock.find('h3').html(initialTitle);
            }
          });
        }

        // Price facets to respect Soft Limit.
        var facetName = $finalPriceBlock.find('ul').attr('data-drupal-facet-id');
        var zeroBasedLimit = settings.facets.softLimit[facetName] - 1;
        $finalPriceBlock.find('li:gt(' + zeroBasedLimit + ')').hide();
      }

      $('.ui-autocomplete').on("touchend",function(e) {
        if (navigator.userAgent.match(/(iPod|iPhone|iPad)/)) {
          e.stopPropagation();
          e.preventDefault();
          if (e.handled !== true) {
            if ($(e.target).hasClass('.autocomplete-suggestion-user-input')) {
              var $userInput = $(e.currentTarget);
            }
            else {
              var $userInput = $(e.currentTarget).find('.autocomplete-suggestion-user-input');
            }
            var input = $userInput.html() + $userInput.siblings('.autocomplete-suggestion-suggestion-suffix').html();
            $('#edit-keywords').val(input);
            $('#views-exposed-form-search-page').submit();
          }
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
              gallery.closest('.alshaya_search_slider').hide();
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
              gallery.closest('.alshaya_search_slider').hide();
              gallery.css('height', '73px');
            }
          });
        }
      });

      // Show/Hide the slider on Mouse hover.
      $('.c-products__item', context).hover(
        function () {
          if ($(window).width() > 1025) {
            $(this).find('.alshaya_search_slider').show();
          }
        },
        function () {
          $(this).find('.alshaya_search_slider').hide();
        }
      );

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
})(jQuery);
