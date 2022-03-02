/**
 * @file
 * Banner margin override..
 */

 (function ($, Drupal) {
   Drupal.behaviors.bannerMarginOverride = {
     attach: function (context, settings) {
       $('.node--type-advanced-page .c-promo__item__override', context).once('bannermargin').each(function () {
         $(this).parents('.c-promo__item').addClass('c-promo__item__override_processed');
       });
     }
   };
 })(jQuery, Drupal);
