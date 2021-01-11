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

}
