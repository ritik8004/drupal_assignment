(function ($) {
  'use strict';
  Drupal.behaviors.disableKeywordAjax = {
    attach: function (context, settings) {
      $('#block-keywordsearchblock #edit-submit-search', context).off().on('click', function(e){
        $(this).closest('form').submit();
      });
    }
  };
}(jQuery));
