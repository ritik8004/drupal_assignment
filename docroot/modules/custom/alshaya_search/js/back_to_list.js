(function ($) {
  'use strict';
  Drupal.behaviors.backToList = {
    attach: function (context, settings) {
      // On product click, store the product position.
      $('.views-infinite-scroll-content-wrapper .c-products__item').on('click', function () {
        // Prepare object to store details.
        var storage_details = {
          nid: $(this).find('article:first').attr('data-nid'),
          filter: $('div.views-exposed-form.bef-exposed-form form .facets-hidden-container').html(),
          sort: $('select[name="sort_bef_combine"]').val(),
          facet_summary: $('.block-facets-summary').html()
        };

        // As local storage only supports string key/value pair.
        localStorage.setItem(window.location.href, JSON.stringify(storage_details));
      });

      // On page load, apply filter/sort if any.
      $(window).on('load', function() {
        var storage_value = getStorageValues();
        if (typeof storage_value !== 'undefined' && storage_value !== null) {
          if (typeof storage_value.nid !== 'undefined') {
            var needToProcess = needToProcessAndScroll();
            // If we don't need to process.
            if (!needToProcess) {
              // Set timeout because of conflict.
              setTimeout(function(){
                scrollToProduct();
              }, 500);
              return;
            }
            // Doing this due to conflict.
            setTimeout(function(){
              // Apply the filter/sort if any.
              applyFilterSort();
            }, 1000);
          }
        }
      });

      // Doing this on ajax complete as the load more button might not
      // available due to lazy loading.
      $(document).ajaxComplete(function (event, xhr, settings) {
        // Scroll to appropriate position.
        var storage_value = getStorageValues();
        if (typeof storage_value !== 'undefined' && storage_value !== null) {
          if (typeof storage_value.nid !== 'undefined') {
            // If product is not available on the page, means we need to scroll more.
            if ($('.views-infinite-scroll-content-wrapper .c-products__item article[data-nid="' + storage_value.nid + '"]').length < 1) {
              $('.js-pager__items a').trigger("click");
            }
            else {
              setTimeout(function() {
                // Scroll to appropriate product.
                scrollToProduct();
              }, 1000);
            }
          }
        }
      });

      /**
       * Get the storage values.
       *
       * @returns {null}
       */
      function getStorageValues() {
        if (localStorage.getItem(window.location.href)) {
          return JSON.parse(localStorage.getItem(window.location.href));
        }

        return null;
      }

      /**
       * Apply facet sort/filter.
       */
      function applyFilterSort() {
        var storage_value = getStorageValues();
        // If facet filter is available, then apply them + sort.
        if (typeof storage_value !== 'undefined' && storage_value !== null) {
          if (typeof storage_value.filter !== 'undefined' && storage_value.filter.length > 0) {
            // Fill facet hidden input.
            $('div.views-exposed-form.bef-exposed-form form .facets-hidden-container').append(storage_value.filter);
            // Fill facet summary block.
            $('.block-facets-summary').append(storage_value.facet_summary);
            // Fill sort value.
            $('select[name="sort_bef_combine"]').val(storage_value.sort);

            // If desktop device.
            if (!checkNotDesktop()) {
              // Apply sort here.
              $('select[name="sort_bef_combine"]').val(storage_value['sort']);
              var sort_text = $('select[name="sort_bef_combine"][value="' + storage_value['sort'] + '"]').text();
              $('.select2-selection__rendered').html(sort_text);
              $('.select2-selection__rendered').attr('title', sort_text);
            }

            // Clear values from storage so that not work for second time.
            delete storage_value['sort'];
            delete storage_value['filter'];
            delete storage_value['facet_summary'];

            // Update storage value.
            localStorage.setItem(window.location.href, JSON.stringify(storage_value));
            // Trigger apply button click.
            $('div.views-exposed-form.bef-exposed-form form input[type="submit"]').trigger('click');
          }
          // Only if sort is selected.
          else if (typeof storage_value.sort !== 'undefined' && storage_value.sort.length > 0) {
            // If desktop device.
            if (!checkNotDesktop()) {
              // Apply sort here.
              $('select[name="sort_bef_combine"]').val(storage_value['sort']);
              var sort_text = $('select[name="sort_bef_combine"][value="' + storage_value['sort'] + '"]').text();
              $('.select2-selection__rendered').html(sort_text);
              $('.select2-selection__rendered').attr('title', sort_text);
            }

            // Clear sort from storage.
            delete storage_value['sort'];

            // Update storage value.
            localStorage.setItem(window.location.href, JSON.stringify(storage_value));
            // Trigger apply button click.
            $('div.views-exposed-form.bef-exposed-form form input[type="submit"]').trigger('click');
          }
        }
      }

      /**
       * Check if device is not desktop.
       *
       * @returns {Array|{index: number, input: string}}
       */
      function checkNotDesktop() {
        if (navigator.userAgent.match(/(iPad)|(iPhone)|(iPod)|(android)|(webOS)/i)) {
          return true;
        }

        return false;
      }

      /**
       * Check if we need to process/scroll.
       */
      function needToProcessAndScroll() {
        var storage_value = getStorageValues();
        // If product is already available/visible.
        if ($('.views-infinite-scroll-content-wrapper .c-products__item article[data-nid="' + storage_value.nid + '"]').length > 0) {
          // If there any facet filter is applied, then we need to process ajax.
          if (storage_value.filter.length > 0) {
            return true;
          }

          // If any sort is applied.
          if (storage_value.sort.length > 0) {
            var selected_val = $('select[name="sort_bef_combine"]').val();
            // If applied sort is different than default(first select option) value,
            // then we need to process ajax.
            var first_sort_option = $('select[name="sort_bef_combine"] option:first').val();
            if (selected_val != first_sort_option) {
              return true;
            }
          }

          return false;
        }

        return true;
      }

      /**
       * Scroll to the appropriate product.
       */
      function scrollToProduct() {
        var storage_value = getStorageValues();
        var first_visible_product = $('.views-infinite-scroll-content-wrapper article[data-nid="' + storage_value.nid + '"]:visible:first');
        $('html, body').animate({
          scrollTop: ($(first_visible_product).offset().top)
        }, 100);

        // Once scroll to product, clear the storage.
        localStorage.removeItem(window.location.href);
        // Sometimes loader is not hiding, just hide it.
        $('.ajax-progress').hide();
      }
    }
  }

}(jQuery));
