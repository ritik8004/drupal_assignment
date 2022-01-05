(function ($) {

  $(document).ready(function () {
    var $share_label = $('.view-product-item__share--label');
    var $product = $('.view-product-item__inner-container');

    $($share_label).on('click', function () {
      $(this).closest($product).toggleClass('share-overlay');
    });
  });
})(jQuery);
