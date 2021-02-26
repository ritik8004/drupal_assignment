import React from 'react';

import SectionTitle from '../../../utilities/section-title';
import TextField from '../../../utilities/textfield';
import ConditionalView from '../../../common/components/conditional-view';
import { cleanMobileNumber } from '../../../utilities/checkout_util';
import getStringMessage from '../../../utilities/strings';

const FixedFields = ({
  defaultVal, showEmail, showFullName = true, subTitle, type,
}) => {
  let defaultValue = '';
  let shippingAddress = '';
  let shippingAddressValue = '';
  let guestUserFullname = '';
  shippingAddress = JSON.parse(localStorage.getItem('shippingAddress'));
  if (defaultVal.length !== 0 && defaultVal.length !== 'undefined') {
    defaultValue = defaultVal.static;
  } else if (shippingAddress) {
    shippingAddressValue = shippingAddress.static;
    guestUserFullname = shippingAddressValue.firstname.concat(' ').concat(shippingAddressValue.lastname);
  }

  const hasSubTitle = subTitle !== undefined && subTitle.length > 0
    ? 'subtitle-yes' : 'subtitle-no';

  return (
    <div className={`spc-checkout-contact-information ${hasSubTitle}`} id="spc-checkout-contact-info">
      {/* Show contact info only for CnC. */}
      {type === 'cnc'
        && (
        <div className="spc-contact-information-header">
          <SectionTitle>{getStringMessage('contact_information')}</SectionTitle>
          <span className="spc-contact-info-desc">{subTitle}</span>
        </div>
        )}
      <div className="spc-checkout-contact-information-fields">
        <ConditionalView condition={showFullName}>
          <TextField
            type="text"
            required={false}
            name="fullname"
            defaultValue={defaultValue !== '' ? defaultValue.fullname : guestUserFullname}
            className={(defaultValue !== '' && defaultValue.fullname !== '') || (shippingAddressValue !== '' && guestUserFullname !== '') ? 'focus' : ''}
            label={getStringMessage('ci_full_name')}
          />
        </ConditionalView>
        <ConditionalView condition={showEmail}>
          <TextField
            type="email"
            name="email"
            defaultValue={defaultValue !== '' ? defaultValue.email : shippingAddressValue.email}
            className={(defaultValue !== '' && defaultValue.email !== '') || (shippingAddressValue !== '' && shippingAddressValue.email !== '') ? 'focus' : ''}
            label={getStringMessage('ci_email')}
          />
        </ConditionalView>
        <TextField
          type="tel"
          name="mobile"
          defaultValue={defaultValue !== '' ? cleanMobileNumber(defaultValue.telephone) : cleanMobileNumber(shippingAddressValue.telephone)}
          className={(defaultValue !== '' && defaultValue.telephone !== '') || (shippingAddressValue !== '' && shippingAddressValue.telephone !== '') ? 'focus' : ''}
          label={getStringMessage('ci_mobile_number')}
        />
        <input type="hidden" name="address_id" value={defaultValue !== '' && defaultValue.address_id !== null ? defaultValue.address_id : 0} />
      </div>
    </div>
  );
};

export default FixedFields;
