(function ($) {
    Drupal.behaviors.alshaya_acm_js = {
        attach: function (context, settings) {
            $(".acq-cart-summary .content").accordion({
                collapsible: true
            });
        }
    };
})(jQuery);
