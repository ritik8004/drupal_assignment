(function ($) {
  Drupal.behaviors.disableKeywordAjax = {
    attach: function (context, settings) {
      $('#block-keywordsearchblock #edit-submit-search', context).off().on('click', function (e) {
        $(this).closest('form').submit();
      });
      // Convert search textfield to search to prompt keypaad with search button
      // For mobile and tablet.
      $('#block-keywordsearchblock .form-autocomplete', context).attr('type', 'search');
    }
  };
}(jQuery));
