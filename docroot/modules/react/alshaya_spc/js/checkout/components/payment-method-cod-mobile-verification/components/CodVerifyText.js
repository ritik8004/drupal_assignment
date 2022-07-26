import React from 'react';

const CodVerifyText = ({ mobileNumber }) => (
  <div className="cod-mobile-verify-text">
    <span>
      {Drupal.t('Enter the 6-digit OTP code sent to @mobileNumber', { '@mobileNumber': mobileNumber }, { context: 'alshaya_spc' })}
    </span>
    <button
      type="button"
    >
      {Drupal.t('change', {}, { context: 'alshaya_spc' })}
    </button>
  </div>
);

export default CodVerifyText;
