(function ($) {
  'use strict';

  Drupal.behaviors.sizeFacetCopy = {
    attach: function (context, settings) {
      // For now we want to do it for PLP only.
      $('.region__sidebar-first [data-block-plugin-id="facet_block:plp_size"]:first').once('size-copy').each(function () {
        var $wrapper = $(this);
        // Get all available facets.
        $wrapper.find('.facet-item').each(function () {
          var item = $(this).find('a');

          var $div = $('<div />');

          // Add value from hidden anchor to copy.
          $div.attr('data-facet-item-value', $(item).attr('data-drupal-facet-item-value'));

          // Add classes from hidden anchor tag to copy.
          $div.attr('class', $(item).attr('class'));


          var $value = $('.facet-item__value', $(item)).clone();
          $value.find('span').remove();
          var value = $value.html().trim();
          var bandSize = parseInt(value);

          // This is for shop by letters.
          if (isNaN(bandSize)) {
            $div.html(value)
            $('.sfb-letter .sfb-facets-container').append($div);
          }
          // This is for shop by band and cup size.
          else {
            // Find cup size now.
            var cupSize = value.replace(bandSize, '');
            $div.html(bandSize);
            $div.append('<span>' + cupSize + '</span>');
            $('.sfb-band-cup .sfb-facets-container').append($div);
          }
        });

        $('.sfb-facets-container [data-facet-item-value]').on('click', function () {
          var $value = $(this).attr('data-facet-item-value');
          console.log($('.facet-item a[data-drupal-facet-item-value="' + $value + '"]', $wrapper));
          $('.facet-item a[data-drupal-facet-item-value="' + $value + '"]', $wrapper).closest('.facet-item').trigger('click');
        });
      });

    }
  };

}(jQuery));
