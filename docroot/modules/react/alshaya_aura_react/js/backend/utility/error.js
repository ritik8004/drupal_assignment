const auraErrorCodes = {
  /**
   * Error code when card data is empty.
   */
  EMPTY_CARD: 'form_error_empty_card',

  /**
   * Error code when email data is empty.
   */
  EMPTY_EMAIL: 'form_error_email',

  /**
   * Error code when mobile data is empty.
   */
  EMPTY_MOBILE: 'form_error_mobile_number',

  /**
   * Error code when no card is found for given user details.
   */
  NO_CARD_FOUND: 'NO_CARD_FOUND',

  /**
   * Error code when mobile is already registered.
   */
  MOBILE_ALREADY_REGISTERED_CODE: 'mobile_already_registered',

  /**
   * Error message when mobile is already registered.
   */
  MOBILE_ALREADY_REGISTERED_MSG: 'form_error_mobile_already_registered',

  /**
   * Error code when email is already registered.
   */
  EMAIL_ALREADY_REGISTERED_CODE: 'email_already_registered',

  /**
   * Error message when email is already registered.
   */
  EMAIL_ALREADY_REGISTERED_MSG: 'form_error_email_already_registered',

  /**
   * Error message when no mobile number is found for given user details.
   */
  NO_MOBILE_FOUND_MSG: 'form_error_mobile_not_found',

  /**
   * Error code for invalid mobile.
   */
  INVALID_MOBILE: 'INVALID_MOBILE',

  /**
   * Error code for invalid card.
   */
  INVALID_CARDNUMBER: 'INVALID_CARDNUMBER',

  /**
   * Error code for invalid email.
   */
  INVALID_EMAIL: 'INVALID_EMAIL',

  /**
   * Error message when mobile is not registered.
   */
  MOBILE_NOT_REGISTERED: 'mobile_not_registered',

  /**
   * Error message when email is not registered.
   */
  EMAIL_NOT_REGISTERED: 'email_not_registered',

  /**
   * Error message when card number is incorrect.
   */
  INCORRECT_CARDNUMBER: 'invalid_card_number',
};

export default auraErrorCodes;
