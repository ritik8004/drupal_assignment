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
  if (defaultVal.length !== 0 && defaultVal.length !== 'undefined') {
    defaultValue = defaultVal.static;
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
            defaultValue={defaultValue !== '' ? defaultValue.fullname : ''}
            className={defaultValue !== '' && defaultValue.fullname !== '' ? 'focus' : ''}
            label={getStringMessage('ci_full_name')}
          />
        </ConditionalView>
        <ConditionalView condition={showEmail}>
          <TextField
            type="email"
            name="email"
            defaultValue={defaultValue !== '' ? defaultValue.email : ''}
            className={defaultValue !== '' && defaultValue.email !== '' ? 'focus' : ''}
            label={getStringMessage('ci_email')}
          />
        </ConditionalView>
        <TextField
          type="tel"
          name="mobile"
          defaultValue={defaultValue !== '' ? cleanMobileNumber(defaultValue.telephone) : ''}
          className={defaultValue !== '' && defaultValue.telephone !== '' ? 'focus' : ''}
          label={getStringMessage('ci_mobile_number')}
        />
        <input type="hidden" name="address_id" value={defaultValue !== '' && defaultValue.address_id !== null ? defaultValue.address_id : 0} />
      </div>
    </div>
  );
};

export default FixedFields;
