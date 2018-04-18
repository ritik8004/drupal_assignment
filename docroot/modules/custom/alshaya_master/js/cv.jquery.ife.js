/**
 * @file
 * Attaches behaviors for the Clientside Validation jQuery module.
 */

(function ($, Drupal) {
  'use strict';

  /**
   * Attaches jQuery validate behavior to forms.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *  Attaches the outline behavior to the right context.
   */
  Drupal.behaviors.cvJqueryValidateIfe = {
    attach: function (context) {
      $(context).find('.form-item input, .form-item select, .form-item textarea').each(function () {
        // Remove IFE error on focus out only if there is client side error.
        $(this).on('focusout', function () {
          var wrapper = $(this).closest('.form-item');
          if (wrapper.find('label.error:visible').length > 0) {
            wrapper.find('.form-item--error-message').remove();
          }
        });

        // Remove IFE error on change, we will validate again.
        $(this).on('change', function () {
          var wrapper = $(this).closest('.form-item');
          wrapper.find('.form-item--error-message').remove();
          wrapper.removeClass('form-item--error');
        });
      });
    }
  };
})(jQuery, Drupal);
