import React from 'react';

/**
 * Show otp timer and resend button.
 *
 * @todo Implement dynamic timer for otp resend timeout.
 */
const OtpTimer = () => (
  <div className="cod-otp-timmer-wrapper">
    <span>{Drupal.t('Didn\'t receive the code?', {}, { context: 'cod_mobile_verification' })}</span>
    <span>00.60</span>
  </div>
);

export default OtpTimer;
