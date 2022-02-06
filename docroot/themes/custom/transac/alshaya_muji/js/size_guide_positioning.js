(function ($, Drupal) {
  Drupal.behaviors.rePositionSizeGuideLinkJS = {
    attach: function () {
      setTimeout(function rePositionSizeGuideLink() {
        var sizeGuideLink = $('#configurable_ajax .size-guide-form-and-link-wrapper .size-guide-link').first();
        var pipeSpan = $('<span class="size-guide-pipe">|</span>');
        var selectedSizeParent = $(
          '#configurable_ajax .size-guide-form-and-link-wrapper .form-item-configurables-size .select2Option .size-guide-placeholder'
        );
        var appendedSizeGuideLink = $('#configurable_ajax .size-guide-form-and-link-wrapper .select2Option .size-guide-placeholder .size-guide-link');

        if (sizeGuideLink && sizeGuideLink.length && selectedSizeParent && selectedSizeParent.length) {
          if (appendedSizeGuideLink && !appendedSizeGuideLink.length) {
            var sizeGuideLinkClone = sizeGuideLink.clone(true, true);
            pipeSpan.appendTo(selectedSizeParent);
            sizeGuideLinkClone.appendTo(selectedSizeParent);
          }
        }
      }, 0);
    }
  };
})(jQuery, Drupal);
