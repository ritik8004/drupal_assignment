(function ($) {
  'use strict';
  Drupal.behaviors.disableKeywordAjax = {
    attach: function (context, settings) {
      $('#block-keywordsearchblock #edit-submit-search', context).off().on('click', function(e){
        e.preventDefault();
        $(this).closest('form').submit();
      });
    }
  };
}(jQuery));
