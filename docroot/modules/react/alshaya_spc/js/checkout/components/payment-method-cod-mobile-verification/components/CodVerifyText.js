import React from 'react';
import dispatchCustomEvent from '../../../../../../js/utilities/events';
import { formatMobileNumber } from '../../../../utilities/checkout_util';


const CodVerifyText = ({ mobileNumber, otpLength }) => {
  const phoneNumber = formatMobileNumber(mobileNumber);
  return (
    <div className="cod-mobile-otp__text-wrapper">
      <span className="cod-mobile-otp__text">
        {Drupal.t('Enter the @otp_length-digit OTP code sent to', {
          '@otp_length': otpLength,
        }, { context: 'cod_mobile_verification' })}
      </span>
      <span className="cod-mobile-otp__mobile">
        <span className="cod-mobile-otp__mobile-number" dir="ltr">{phoneNumber}</span>
        <button
          type="button"
          className="cod-mobile-otp__mobile-change"
          onClick={() => dispatchCustomEvent('openAddressContentPopup', {
            enabledFieldsWithMessages: {
              mobile: Drupal.t('Please update mobile number', {}, { context: 'cod_mobile_verification' }),
            },
          }, {})}
        >
          {Drupal.t('Change', {}, { context: 'cod_mobile_verification' })}
        </button>
      </span>
    </div>
  );
};

export default CodVerifyText;
