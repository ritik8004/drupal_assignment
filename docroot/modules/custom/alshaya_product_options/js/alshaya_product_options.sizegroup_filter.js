/**
 * @file
 * PLP - Size group Filters Panel & Facets JS file.
 */

(function ($, Drupal) {

  Drupal.behaviors.sizegroupFilter = {
    // Opens the selected grand parent filter value using
    // #all-filter-active-facet-sort text value.
    openSelectedSizeGroupFilter: function () {
      var active_facet_sort = $('#all-filter-active-facet-sort').val();
      var active_facet_sort_elements = active_facet_sort.split(','); // convert to array of values

      if(active_facet_sort_elements.length > 1) {
        for(var i = 0; i < active_facet_sort_elements.length; i++) {
          // Normal execution in case of the facet block selector.
          if($('.all-filters #' + active_facet_sort_elements[i]).hasClass('block-facets')) {
            $('.all-filters #' + active_facet_sort_elements[i]).addClass('show-facet');
          } else { // Add class to the parent of selected children if the selector is not block facet.
            $('.all-filters #block-plpsize >ul >li, .all-filters #block-promosize >ul >li').hide();
            $('.all-filters [id=' + active_facet_sort_elements[i] + ']').parent().addClass('show-facet');
          }
        }
      }
    },

    attach: function (context, settings) {
      // Attach on attach behaviour call.
      Drupal.behaviors.sizegroupFilter.openSelectedSizeGroupFilter();

      // Override reopen facet logic.
      $('.sticky-filter-wrapper').once('facet-show-all-for-sizegroup-processed').on('click', '.show-all-filters', function () {
        Drupal.behaviors.sizegroupFilter.openSelectedSizeGroupFilter();
      });

      $('.sticky-filter-wrapper').once('product-option-show-size-filter').on('click tap', '#block-plpsize ul li, #block-promosize ul li', function (context) {
        var $oldOpenedEl = $('.is-open', $(this).parent());
        if($(this).hasClass('is-open')) {
          $oldOpenedEl.removeClass('is-open');
          return;
        }

        $oldOpenedEl.removeClass('is-open');
        $(this).addClass('is-open');
      });

      $('.all-filters').once('plp-size-group').on('click', '#block-plpsize > ul > li, #block-promosize > ul > li', function (event) {
        // Update the title on click of facet.
        var facet_title = $(event.target).text();

        if($(event.target).closest('.sizegroup').length > 0) {
          facet_title = $(event.target).closest('.sizegroup').parent().children('span').text();
          $('.filter-sort-title').html(facet_title);

          // Return for normal execution.
          return;
        }

        $('.filter-sort-title').html(facet_title);
        // Only show current facet and hide all others.
        $(this).removeClass('show-facet');
        $('.all-filters #block-plpsize >ul >li, .all-filters #block-promosize >ul >li').hide();
        $(this).addClass('show-facet');
        // Store the sizegroup id for ajax.
        $('main #all-filter-active-facet-sort').attr('data-size-group-id', $(this).find('ul').attr('id'));
        // Pass comma separated ids for the elements to make visible.
        // This is separate by , so that other JS logic bypasses it
        // and this will be handled by this behaviour specifically
        // See openSelectedSizeGroupFilter function.
        $('#all-filter-active-facet-sort', $(this).closest('.filter__inner').closest('.block')).val($(this).children('ul').attr('id') + ',' + $(this).closest('.c-facet').attr('id'));

        // Stop event bubbling and normal execution.
        event.stopPropagation();
        event.preventDefault();
      });

      $('.all-filters').once('facet-all-back-for-sizegroup-processed').on('click', '.facet-all-back', function () {
        var $selectedGrandChild = $('.all-filters #block-plpsize >ul >li.show-facet, .all-filters #block-promosize >ul >li.show-facet');

        // If any grand child is open go to this custom logic.
        if($selectedGrandChild.length) {
          // Reset what is done in the above click handler to reset the state.
          $('.all-filters #block-plpsize >ul >li, .all-filters #block-promosize >ul >li').show();
          // Select it's parent (child of main grand parent) and trigger click on it.
          // This is done in order to mimmick the back state.
          $selectedGrandChild.closest('.c-facet').find('.c-facet__title').trigger('click');
          // Reset the show facet class to reset to original state.
          $('.all-filters #block-plpsize >ul >li.show-facet, .all-filters #block-promosize >ul >li.show-facet').removeClass('show-facet');
          $('main #all-filter-active-facet-sort').attr('data-size-group-id', '');
        }

        // Stop event bubbling and normal execution.
        event.stopPropagation();
        event.preventDefault();
      });
    }
  }
})(jQuery, Drupal);
