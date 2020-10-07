/**
 * @file
 * Overriding email address validator.
 */

(function ($, Drupal) {
  'use strict';

  $.validator.methods.email = function (value, element) {
    if (this.optional(element) && value.length === 0) {
      return true;
    }

    var complex = / ( ?: [a - z0 - 9!#$ % & '*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&' * + /= ? ^_`{ | }~ - ] + ) * | "(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@( ?: ( ?: [a - z0 - 9]( ?: [a - z0 - 9 - ] * [a - z0 - 9]) ? \.) + [a - z0 - 9]( ?: [a - z0 - 9 - ] * [a - z0 - 9]) ? | \[( ?: ( ?: 25[0 - 5] | 2[0 - 4][0 - 9] | [01] ? [0 - 9][0 - 9] ? )\.){3}( ?: 25[0 - 5] | 2[0 - 4][0 - 9] | [01] ? [0 - 9][0 - 9] ? | [a - z0 - 9 - ] * [a - z0 - 9] : ( ?: [\x01 - \x08\x0b\x0c\x0e - \x1f\x21 - \x5a\x53 - \x7f] | \\[\x01 - \x09\x0b\x0c\x0e - \x7f]) + )\]) / i;

    var input = document.createElement('input');
    input.type = 'email';
    input.value = value;

    return typeof input.checkValidity == 'function' ? input.checkValidity() && complex.test(value) : complex.test(value);
  };

  // Validation for special characters.
  $.validator.addMethod("specialchar", function (value, element ) {
    return this.optional(element) || /^[^\^`!@#$%&*()_+":?><,./;'[\]{}] + $ / .test(value);
  });

  // Validation for password.
  $.validator.addMethod("passvalidate", function (value, element ) {
    // Password must have a number (including arabic), a special character, must allow characters from english as well
    // as arabic language and minimum 7 characters.
    return this.optional(element) || / ( ? = (.*[\۰۱۲۳٤٥٦٧۸۹ | 0 - 9]))( ? = .*[\!@#$ % ^ & * ()\\[\]{}\ - _ += ~` | : ;"' < > ,./ ? ])( ? !.*[\s])( ? = .*[\u0600 - \u06FF | a - zA - Z])( ? = (.*)).{7,} / .test(value);
  });

})(jQuery, Drupal);
