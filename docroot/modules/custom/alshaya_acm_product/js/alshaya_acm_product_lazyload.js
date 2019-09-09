/**
 * @file
 */

(function ($, Drupal) {
  var products = $('.lazyload-product');

  $.each(products, function(key, product) {
    var product_nid = $(product).find('.placeholder-lazyload-product').attr('data-id');
    var productRequest = $.ajax({
      url: "/product_listing/" + product_nid,
      method: "GET",
      dataType: "html",
      cache: "TRUE",
    });
    productRequest.done(function( productDetails ) {
      product.innerHTML = productDetails;
      blazy.revalidate();
    });
  });

})(jQuery, Drupal);