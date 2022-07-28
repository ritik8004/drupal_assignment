import React from 'react';

const CodVerifyText = ({ mobileNumber, otpLength }) => (
  <div className="cod-mobile-verify-text">
    <span>
      {Drupal.t('Enter the @otpLength-digit OTP code sent to @mobileNumber', {
        '@mobileNumber': mobileNumber,
        '@otpLength': otpLength,
      }, { context: 'cod_mobile_verification' })}
    </span>
    <button type="button">
      {Drupal.t('change', {}, { context: 'cod_mobile_verification' })}
    </button>
  </div>
);

export default CodVerifyText;
