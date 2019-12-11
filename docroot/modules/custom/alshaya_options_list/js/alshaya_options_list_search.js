(function ($, Drupal) {
  Drupal.behaviors.alshaya_options_list_filter = {
    attach: function (context, settings) {
      $("#alshaya-options-list-autocomplete-form").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $('.options-list .attribute-filter-name-list .title').filter(function() {
          var parent = $(this).parents('li.level-1');
          var noResults = $('.attribute-filter-name-list.no-result-container');
          // By default, show parents and hide no results.
          parent.show();
          noResults.hide();

          // Toggle values based on input.
          $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);

          // Now find all visible children.
          var child = parent.find('li:visible');

          // If all children of the element are hidden, hide parent.
          if(!child.length){
            parent.hide();
            var siblings = parent.siblings().filter(':visible');

            // If all siblings are not visible, show no results.
            if(!siblings.length){
              noResults.show();
            }
          }
        });
      });
    }
  }
})(jQuery, Drupal);
