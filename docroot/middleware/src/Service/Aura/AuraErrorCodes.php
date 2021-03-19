<?php

namespace App\Service\Aura;

/**
 * Class AuraErrorCodes.
 *
 * Class contains error codes we send to FE.
 */
final class AuraErrorCodes {

  /**
   * Error code when card data is empty.
   */
  const EMPTY_CARD = 'form_error_empty_card';

  /**
   * Error code when email data is empty.
   */
  const EMPTY_EMAIL = 'form_error_email';

  /**
   * Error code when mobile data is empty.
   */
  const EMPTY_MOBILE = 'form_error_mobile_number';

  /**
   * Error code when no card is found for given user details.
   */
  const NO_CARD_FOUND = 'NO_CARD_FOUND';

  /**
   * Error code when mobile is already registered.
   */
  const MOBILE_ALREADY_REGISTERED_CODE = 'mobile_already_registered';

  /**
   * Error message when mobile is already registered.
   */
  const MOBILE_ALREADY_REGISTERED_MSG = 'form_error_mobile_already_registered';

  /**
   * Error code when email is already registered.
   */
  const EMAIL_ALREADY_REGISTERED_CODE = 'email_already_registered';

  /**
   * Error message when email is already registered.
   */
  const EMAIL_ALREADY_REGISTERED_MSG = 'form_error_email_already_registered';

  /**
   * Error message when no mobile number is found for given user details.
   */
  const NO_MOBILE_FOUND_MSG = 'form_error_mobile_not_found';

  /**
   * Error code for invalid mobile.
   */
  const INVALID_MOBILE = 'INVALID_MOBILE';

  /**
   * Error code for invalid card.
   */
  const INVALID_CARDNUMBER = 'INVALID_CARDNUMBER';

  /**
   * Error code for invalid email.
   */
  const INVALID_EMAIL = 'INVALID_EMAIL';

  /**
   * Error message when mobile is not registered.
   */
  const MOBILE_NOT_REGISTERED = 'mobile_not_registered';

  /**
   * Error message when email is not registered.
   */
  const EMAIL_NOT_REGISTERED = 'email_not_registered';

  /**
   * Error message when card number is incorrect.
   */
  const INCORRECT_CARDNUMBER = 'invalid_card_number';

}
