(function ($) {
  'use strict';
  Drupal.behaviors.backToList = {
    attach: function (context, settings) {
      // On product click, store the product position.
      $('.views-infinite-scroll-content-wrapper .c-products__item').on('click', function () {
        // Prepare object to store details.
        var storage_details = {
          nid: $(this).find('article:first').attr('data-nid'),
          filter: $('.block-views-exposed-filter-blockalshaya-product-list-block-1 form .facets-hidden-container').html(),
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
            window.scrollTo(0, 0);
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
                var ee = $('.views-infinite-scroll-content-wrapper article[data-nid="' + storage_value.nid + '"]:visible:first');
                $('html, body').animate({
                  scrollTop: ($(ee).offset().top)
                }, 100);

                // Once scroll to product, clear the storage.
                localStorage.removeItem(window.location.href);
                // Sometimes loader is not hiding, just hide it.
                $('.ajax-progress').hide();
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
            $('.block-views-exposed-filter-blockalshaya-product-list-block-1 form .facets-hidden-container').append(storage_value.filter);
            // Fill facet summary block.
            $('.block-facets-summary').append(storage_value.facet_summary);
            // Fill sort value.
            $('select[name="sort_bef_combine"]').val(storage_value.sort);

            // If desktop device.
            if (!checkNotDesktop()) {
              // Apply sort here.
              $('#edit-sort-bef-combine').val(storage_value['sort']);
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
            $('#edit-submit-alshaya-product-list').trigger('click');
          }
          // Only if sort is selected.
          else if (typeof storage_value.sort !== 'undefined' && storage_value.sort.length > 0) {
            // If desktop device.
            if (!checkNotDesktop()) {
              // Apply sort here.
              $('#edit-sort-bef-combine').val(storage_value['sort']);
              var sort_text = $('select[name="sort_bef_combine"][value="' + storage_value['sort'] + '"]').text();
              $('.select2-selection__rendered').html(sort_text);
              $('.select2-selection__rendered').attr('title', sort_text);
            }

            // Clear sort from storage.
            delete storage_value['sort'];

            // Update storage value.
            localStorage.setItem(window.location.href, JSON.stringify(storage_value));
            // Trigger apply button click.
            $('#edit-submit-alshaya-product-list').trigger('click');
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
    }
  }

}(jQuery));
