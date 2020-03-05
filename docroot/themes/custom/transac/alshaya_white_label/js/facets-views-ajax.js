/**
 * @file
 * Facets Views AJAX handling.
 * Override drupal.facets.views-ajax library to update facetsViewsAjax behavior to open accordion if facet-item
 * has child items, else update views output.
 */

/**
 * @name FacetsViewsAjaxSettings
 * @property {String} view_id
 * @property {String} current_display_id
 * @property {String} view_base_path
 */

/**
 * @property {FacetsViewsAjaxSettings[]} drupalSettings.facets_views_ajax
 */


(function ($, Drupal) {
  'use strict';

  /**
   * Trigger views AJAX refresh on click.
   */
  Drupal.behaviors.facetsViewsAjax = {
    attach: function (context, settings) {
      var update_summary = false;
      if (settings.facets_views_ajax.facets_summary_ajax) {
        update_summary = true;
      }
      $.each(settings.facets_views_ajax, function (facetId, facetSettings) {

        // Get the View for the current facet.
        var view = $('.view-id-' + facetSettings.view_id + '.view-display-id-' + facetSettings.current_display_id).first();
        var dom_id_start = 'js-view-dom-id-';
        var current_dom_id = $.map(view.attr('class').split(' '), function (v, i) {
          if (v.indexOf(dom_id_start) > -1) {
            return v.slice(dom_id_start.length, v.length);
          }
        });

        if (typeof Drupal.views.instances['views_dom_id:' + current_dom_id] === 'undefined') {
          return;
        }

        // Get all ajax facets block from the current page.
        var facets_blocks = [];
        $('.block-facets-ajax').each(function (index) {
          var dom_id_start = 'js-facet-dom-id-';
          var facet_block_id = $.map($(this).attr('class').split(' '), function (v, i) {
            if (v.indexOf(dom_id_start) > -1) {
              return v.slice(dom_id_start.length, v.length);
            }
          }).join();
          facets_blocks.push(facet_block_id);
        });

        if (update_summary && (facetId === 'facets_summary_ajax')) {
          $('[data-drupal-facets-summary-id=' + facetSettings.facets_summary_id + ']').children('ul').find('li').once().click(function (e) {
            e.preventDefault();
            // If facet-item has child items, open accordion.
            if ($(this).hasClass('facet-item--expanded')) {
              accordionExpandedFacetItem($(this));
            }
            // If facet-item does not has child items, update views output.
            else {
              var facetLink = $(this).find('a');
              updateFacetsView(facetLink, facets_blocks, current_dom_id, update_summary, settings);
            }
          });
        }
        else {
          $('[data-drupal-facet-id=' + facetId + ']').find('.facet-item').once().click(function (e) {
            e.preventDefault();
            // If facet-item has child items, open accordion.
            if ($(this).hasClass('facet-item--expanded')) {
              accordionExpandedFacetItem($(this));
            }
            // If facet-item does not has child items, update views output.
            else {
              var facetLink = $(this).find('a');
              updateFacetsView(facetLink, facets_blocks, current_dom_id, update_summary, settings);
            }
            e.stopPropagation();
          });
        }
      });
    }
  };

  // Helper function to update views output & Ajax facets.
  var updateFacetsView = function (facetLink, facets_blocks, current_dom_id, update_summary_block, settings) {
    var views_parameters = Drupal.Views.parseQueryString(facetLink.attr('href'));
    var views_arguments = Drupal.Views.parseViewArgs(facetLink.attr('href'), 'search');
    views_parameters.q = facetLink.attr('href');
    var views_settings = $.extend(
      {},
      Drupal.views.instances['views_dom_id:' + current_dom_id].settings,
      views_arguments,
      views_parameters
    );

    // Kind of custom requirement here so coming from custom patch.
    // We are using sort from exposed filters which is coming from exposed block.
    // Facets AJAX is not yet ready and coming from patch.
    // What we need is to ensure selected sort is sent in AJAX call.
    if ($('.c-content__region [data-drupal-selector="edit-sort-bef-combine"]').length > 0) {
      views_settings.sort_bef_combine = $('.c-content__region [data-drupal-selector="edit-sort-bef-combine"] [name="sort_bef_combine"]:checked').val();
    }

    // Update View.
    var views_ajax_settings = Drupal.views.instances['views_dom_id:' + current_dom_id].element_settings;
    views_ajax_settings.submit = views_settings;
    views_ajax_settings.url = Drupal.url('views/ajax') + '?q=' + facetLink.attr('href');

    // Update facet blocks.
    var facet_settings = {
      url: Drupal.url('facets-block/ajax'),
      type: 'GET',
      submit: {
        facet_link: facetLink.attr('href'),
        facets_blocks: facets_blocks
      }
    };

    Drupal.ajax(views_ajax_settings).execute();

    if (update_summary_block) {
      var facet_summary_wrapper_id = $('[data-drupal-facets-summary-id=' + settings.facets_views_ajax.facets_summary_ajax.facets_summary_id + ']').attr('id');
      var facet_summary_block_id = '';
      if (facet_summary_wrapper_id.indexOf('--') !== -1) {
        facet_summary_block_id = facet_summary_wrapper_id.substring(0, facet_summary_wrapper_id.indexOf('--')).replace('block-', '');
      }
      else {
        facet_summary_block_id = facet_summary_wrapper_id.replace('block-', '');
      }
      facet_settings.submit.update_summary_block = update_summary_block;
      facet_settings.submit.facet_summary_block_id = facet_summary_block_id;
      facet_settings.submit.facet_summary_wrapper_id = settings.facets_views_ajax.facets_summary_ajax.facets_summary_id;
    }
    facet_settings.submit.active_facet = facetLink.closest('.block-facets-ajax').attr('data-block-plugin-id');

    Drupal.ajax(facet_settings).execute();
  };

  // Helper function to open accordion on click of expanded facet-item.
  var accordionExpandedFacetItem = function (facetItem) {
    if (facetItem.hasClass('active') || facetItem.hasClass('facet-item--active-trail')) {
      if (facetItem.hasClass('facet-item--active-trail')) {
        facetItem.removeClass('facet-item--active-trail');
      }
      facetItem.removeClass('active');
      // We want to run this only on main page facets.
      facetItem.find('ul').slideUp();
    }
    else {
      var category_facet_search_block = facetItem.parents('.block-facet-blockcategory-facet-search');
      var facet_item_height = facetItem.find('label').outerHeight();
      var category_dropdown_height_scroll = facetItem.parent('ul').scrollTop();
      var category_height = category_facet_search_block.outerHeight();
      var category_height_offset = category_facet_search_block.offset().top;
      var category_dropdown_height = facetItem.parent('ul').outerHeight();
      var calc_offset = category_height + category_height_offset + category_dropdown_height;

      facetItem.addClass('active');
      facetItem.find('ul').slideDown();
      // If sale item children is not in view, scroll to show children.
      if (facetItem.offset().top > (calc_offset - (facet_item_height * 1.5))) {
        // For mobile portrait, scroll to show 3.5 items so that user will know there are more items to scroll if any.
        if ($(window).height() > 480) {
          // Default value of duration in slideDown() is 400.
          setTimeout(function () {
            facetItem.parent('ul').scrollTop( category_dropdown_height_scroll + (facet_item_height * 3.5) );
          }, 400);
        }
        // For mobile landscape, scroll to show 1.5 items so that user will know there are more items to scroll if any.
        else {
          // Default value of duration in slideDown() is 400.
          setTimeout(function () {
            facetItem.parent('ul').scrollTop( category_dropdown_height_scroll + (facet_item_height * 1.5) );
          }, 400);
        }
      }
    }
  };

})(jQuery, Drupal);
