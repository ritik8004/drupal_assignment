(function ($, Drupal) {
  Drupal.behaviors.delayed_submit = {
    attach: function (context, settings) {
      $('input.delayed-search-submit').each(function () {
        var $self = $(this);
        var timeout = null;
        var delay = $self.data('delay') || 1000;
        var triggerEvent = $self.data('event') || "endTyping";

        $self.unbind('keyup').keyup(function () {
          clearTimeout(timeout);
          timeout = setTimeout(function () {
            $self.trigger(triggerEvent);
          }, delay);
          this.focus();
        });
      });
    }
  }
})(jQuery, Drupal);