(function ($) {
  'use strict';
  Drupal.behaviors.alshayaSearch = {
    attach: function (context, settings) {
      $('#edit-sort-bef-combine option[value="search_api_relevance ASC"]').remove();
      // Do not allow search form submit on empty search text.
      $('form[data-bef-auto-submit-full-form]', context).submit(function (e) {
        var $keyword = $(this).find('input[name="keywords"]');
        if (typeof $keyword.val() == 'undefined' || $.trim($keyword.val()) === '') {
          e.preventDefault();
        }
      });

      // Ajax command to update search result header count.
      $.fn.alshayaSearchHeaderUpdate = function (data) {
        // If search page.
        if ($('.view-id-search').length !== 0) {
          // Update the header result count.
          var header_result = $('.view-id-search .view-header').html();
          $('.search-count').html(header_result);
        }
      };
    }
  };

  Drupal.behaviors.alshayaFacets = {
    attach: function (context, settings) {
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
                // Hide show more if above keyword has some data.
                if (settings.facets.softLimit !== undefined) {
                  $(this).parent().find('.facets-soft-limit-link').hide();
                }
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

      // Poll the DOM to check if the show more/less link is avaialble, before placing it inside the ul.
      var i = setInterval(function () {
        if ($('.c-search aside .block-facet--checkbox a.facets-soft-limit-link').length) {
          clearInterval(i);
          $('aside .block-facet--checkbox').each(function () {
            var softLink = $(this).find('a.facets-soft-limit-link');
            softLink.insertAfter($(this).find('ul'));
          });
        }
      }, 100);

      var j = setInterval(function () {
        if ($('.c-search .region__content .region__sidebar-first .block-facet--checkbox a.facets-soft-limit-link').length) {
          clearInterval(j);
          $('.region__content .region__sidebar-first .block-facet--checkbox').each(function () {
            var softLink = $(this).find('a.facets-soft-limit-link');
            softLink.addClass('processed');
            softLink.insertAfter($(this).find('ul'));
          });
        }
      }, 100);

      // Change the title of facet when open.
      var priceCurrency = settings.alshaya_search_price_currency;
      if (priceCurrency) {
        var initialTitle = $('#block-finalprice h3').html();
        $('#block-finalprice > h3').on('click', function() {
          if ($(this).hasClass('ui-state-active')) {
            $('#block-finalprice h3').html(initialTitle + ' (' + priceCurrency + ')');
          }
          else {
            $('#block-finalprice h3').html(initialTitle);
          }
        });
      }

    }
  };

  Drupal.behaviors.searchSlider = {
    attach: function (context, settings) {
      // Convert the list to slider.
      $('.search-lightSlider', context).once('alshayaSearchSlider').each(function () {
        var gallery = $(this);
        $(this, context).lightSlider({
          vertical: false,
          item: 4,
          slideMargin: 6,
          autoWidth: true,
          onSliderLoad: function() {
            gallery.closest('.alshaya_search_slider').hide();
            gallery.css('height', '73px');
          }
        });
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

      $.fn.alshayaAttachSearchSlider = function () {
        Drupal.attachBehaviors(context);
      };
    }
  };
})(jQuery);
