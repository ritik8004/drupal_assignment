/**
 * @file
 */

(function ($, Drupal) {
  'use strict';
var products = document.querySelectorAll('.lazyload-product');

products.forEach(function(product) {
  var product_details = $(this).find('.placeholder-lazyload-product');
  var product_nid = product_details.attr('data-id');
  console.log(product_nid);
  product.innerHTML = '<div>Empty test</div>';
});

})(jQuery, Drupal);