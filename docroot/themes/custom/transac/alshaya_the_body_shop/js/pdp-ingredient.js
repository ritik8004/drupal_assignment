/**
 * @file
 * PDP INGREDIENT JS.
 */

(function ($, Drupal) {

  Drupal.behaviors.pdpIngredientJs = {
    attach: function (context, settings) {
      if (context === document) {
        if ($(window).width() > 767) {
          // Toggle for Product ingredients.
          $('.read-more-ingredients-link').once('readmore-ingredients').on('click', function () {
            // Close click and collect all stores wrapper if open.
            if ($('.click-collect-all-stores').hasClass('desc-open')) {
              $('.click-collect-all-stores').toggleClass('desc-open');
            }
            $(this).siblings('.technical_ingredients_description').toggleClass('desc-open');
          });
        }
        else {
          // Show technical ingredients on PDP on click of Read more.
          $('.read-more-ingredients-link').once('readmore-ingredients-mobile').on('click', function () {
            $(this).siblings('.technical_ingredients_description').slideToggle('slow');
            $(this).siblings('.technical_ingredients_description').append('<span class="show-less-ingredient-link">' + Drupal.t('show less') + '</span>');
            $(this).hide();
          });

          // Hide technical ingredients on PDP on click of show less.
          $(document).on('click', '.show-less-ingredient-link', function () {
            $(this).parent().slideToggle('slow');
            $(this).parent().siblings('.read-more-ingredients-link').show();
            $(this).remove();
          });
        }

        $('.ingredient-modal-close').once('readmore-close').on('click', function () {
          $(this).parents('.technical_ingredients_description').toggleClass('desc-open');
        });

        /**
         * Function to create accordion.
         *
         * @param {object} element
         *   The HTML element inside which we want to make accordion.
         */
        Drupal.convertIntoAccordion = function (element) {
          element.once('accordion-init').accordion({
            heightStyle: 'content',
            collapsible: true,
            active: false
          });
        };

        // Accordion for ingredients section on PDP.
        $('.content--ingredient').find('.pdp-description-accordion').each(function () {
          Drupal.convertIntoAccordion($(this));
        });
      }
    }
  };
})(jQuery, Drupal);
