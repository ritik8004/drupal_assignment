import React from 'react';

const CodVerifyText = ({ mobileNumber, otpLength }) => (
  <div className="cod-mobile-verify-text">
    <span>
      {Drupal.t('Enter the @otp_length-digit OTP code sent to @mobile_number', {
        '@mobile_number': mobileNumber,
        '@otp_length': otpLength,
      }, { context: 'cod_mobile_verification' })}
    </span>
    <button type="button">
      {Drupal.t('change', {}, { context: 'cod_mobile_verification' })}
    </button>
  </div>
);

export default CodVerifyText;
