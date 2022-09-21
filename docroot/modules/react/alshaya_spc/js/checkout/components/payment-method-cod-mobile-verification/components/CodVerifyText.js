import React from 'react';
import dispatchCustomEvent from '../../../../../../js/utilities/events';
import { formatMobileNumber } from '../../../../utilities/checkout_util';


const CodVerifyText = ({ mobileNumber, otpLength }) => {
  const phoneNumber = formatMobileNumber(mobileNumber);
  return (
    <div className="cod-mobile-otp__text-wrapper">
      <span className="cod-mobile-otp__text">
        {Drupal.t('Enter the @otp_length-digit OTP code sent to @mobile_number', {
          '@mobile_number': phoneNumber,
          '@otp_length': otpLength,
        }, { context: 'cod_mobile_verification' })}
      </span>
      <button
        type="button"
        className="cod-mobile-otp__btn-link"
        onClick={() => dispatchCustomEvent('openAddressContentPopup', {
          enabledFieldsWithMessages: {
            mobile: Drupal.t('Please update mobile number', {}, { context: 'cod_mobile_verification' }),
          },
        }, {})}
      >
        {Drupal.t('Change', {}, { context: 'cod_mobile_verification' })}
      </button>
    </div>
  );
};

export default CodVerifyText;
