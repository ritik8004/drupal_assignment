import React from 'react';
import dispatchCustomEvent from '../../../../../../js/utilities/events';

const CodVerifyText = ({ mobileNumber, otpLength }) => (
  <div>
    <span className="cod-mobile-verification__text">
      {Drupal.t('Enter the @otp_length-digit OTP code sent to @mobile_number', {
        '@mobile_number': mobileNumber,
        '@otp_length': otpLength,
      }, { context: 'cod_mobile_verification' })}
    </span>
    <button
      type="button"
      className="cod-mobile-verification__btn-link"
      onClick={() => dispatchCustomEvent('openAddressContentPopup', {
        enabledFieldsWithMessages: {
          mobile: Drupal.t('Please update mobile number', {}, { context: 'cod_mobile_verification' }),
        },
      }, {})}
    >
      {Drupal.t('change', {}, { context: 'cod_mobile_verification' })}
    </button>
  </div>
);

export default CodVerifyText;
