import React from 'react';
import dispatchCustomEvent from '../../../../../../js/utilities/events';
import { formatMobileNumber } from '../../../../utilities/checkout_util';

/**
 * Show verified text for COD paymennt mobile number and edit button.
 */
const CodVerifiedText = ({ mobileNumber }) => {
  const phoneNumber = formatMobileNumber(mobileNumber);
  return (
    <div className="cod-mobile-otp__verified">
      <span className="cod-mobile-otp__verified_mobile">
        {phoneNumber}
      </span>
      <span className="cod-mobile-otp__verified_message">
        {Drupal.t('Verified', {}, { context: 'cod_mobile_verification' })}
      </span>
      <button
        type="button"
        onClick={() => dispatchCustomEvent('openAddressContentPopup', {
          enabledFieldsWithMessages: {
            mobile: Drupal.t('Please update mobile number', {}, { context: 'cod_mobile_verification' }),
          },
        }, {})}
        className="cod-mobile-otp__verified_edit"
      >
        {Drupal.t('Edit', {}, { context: 'cod_mobile_verification' })}
      </button>
    </div>
  );
};

export default CodVerifiedText;
