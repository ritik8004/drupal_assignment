/**
 * @file
 * Alshaya brand carousel used on advanced pages.
 */

 /* global isRTL */

 (function ($, Drupal) {
   'use strict';

   Drupal.behaviors.alshayaBrandCarousel = {
     attach: function (context, settings) {

       var brandCarouselSettings = drupalSettings.brand_carousel_items_settings;
       var alshayaBrandCarousel = {
         arrows: true,
         useTransform: false,
         slidesToShow: brandCarouselSettings.brand_carousel_slidesToShow,
         slidesToScroll: brandCarouselSettings.brand_carousel_slidesToScroll,
         focusOnSelect: false,
         touchThreshold: 1000,
         infinite: true
       };

       if ($(window).width() > 767) {
         if ($('.alshaya_brand_carousel .brand_logos').length > brandCarouselSettings.brand_carousel_slidesToShow) {
           if (isRTL()) {
             $('.alshaya_brand_carousel').once().slick(
               $.extend({}, alshayaBrandCarousel, {rtl: true})
             );
           }
           else {
             $('.alshaya_brand_carousel').once().slick(alshayaBrandCarousel);
           }
         }
       }
     }
   };
 })(jQuery, Drupal);
