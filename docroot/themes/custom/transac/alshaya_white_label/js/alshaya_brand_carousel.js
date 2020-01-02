/**
 * @file
 * Alshaya brand carousel used on advanced pages.
 */

 /* global isRTL */

 (function ($, Drupal) {
   'use strict';

   Drupal.behaviors.alshayaBrandCarousel = {
     attach: function (context, settings) {

       var alshayaBrandCarousel = {
         arrows: true,
         useTransform: false,
         slidesToShow: 5,
         slidesToScroll: 3,
         focusOnSelect: false,
         touchThreshold: 1000,
         infinite: false
       };

       if ($(window).width() > 1025) {
         if ($('.alshaya_brand_carousel .brand_logos').length > 5) {
           if (isRTL()) {
             $('.alshaya_brand_carousel').slick(
               $.extend({}, alshayaBrandCarousel, {rtl: true})
             );
           }
           else {
             $('.alshaya_brand_carousel').slick(alshayaBrandCarousel);
           }
         }
       }
     }
   };
 })(jQuery, Drupal);
