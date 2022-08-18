import React from 'react';
import dispatchCustomEvent from '../../../../../../js/utilities/events';

/**
 * Show verified text for COD paymennt mobile number and edit button.
 */
const CodVerifiedText = ({ mobileNumber }) => (
  <div className="cod-mobile-otp">
    <span className="cod-mobile-otp__verified_mobile">
      {mobileNumber}
    </span>
    <span className="cod-mobile-otp__verified_message">
      {Drupal.t('Verified', {}, { context: 'cod_mobile_verification' })}
    </span>
    <span className="cod-mobile-otp__verified_edit">
      <button
        type="button"
        onClick={() => dispatchCustomEvent('openAddressContentPopup', {
          enabledFieldsWithMessages: {
            mobile: Drupal.t('Please update mobile number', {}, { context: 'cod_mobile_verification' }),
          },
        }, {})}
      >
        {Drupal.t('Edit', {}, { context: 'cod_mobile_verification' })}
      </button>
    </span>
  </div>
);

export default CodVerifiedText;
