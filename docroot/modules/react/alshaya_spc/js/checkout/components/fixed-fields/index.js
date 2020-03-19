import React from 'react';

import SectionTitle from '../../../utilities/section-title';
import TextField from '../../../utilities/textfield';
import ConditionalView from '../../../common/components/conditional-view';
import { cleanMobileNumber } from '../../../utilities/checkout_util';

const FixedFields = ({
  default_val, showEmail, showFullName = true, subTitle,
}) => {
  let defaultVal = '';
  if (default_val.length !== 0 && default_val.length !== 'undefined') {
    defaultVal = default_val.static;
  }

  const hasSubTitle = subTitle !== undefined && subTitle.length > 0
    ? 'subtitle-yes' : 'subtitle-no';

  return (
    <div className={`spc-checkout-contact-information ${hasSubTitle}`} id="spc-checkout-contact-info">
      <div className="spc-contact-information-header">
        <SectionTitle>{Drupal.t('contact information')}</SectionTitle>
        <span className="spc-contact-info-desc">{subTitle}</span>
      </div>
      <div className="spc-checkout-contact-information-fields">
        <ConditionalView condition={showFullName}>
          <TextField
            type="text"
            required={false}
            name="fullname"
            defaultValue={defaultVal !== '' ? defaultVal.fullname : ''}
            className={defaultVal !== '' && defaultVal.fullname !== '' ? 'focus' : ''}
            label={Drupal.t('Full Name')}
          />
        </ConditionalView>
        <ConditionalView condition={showEmail}>
          <TextField
            type="email"
            name="email"
            defaultValue={defaultVal !== '' ? defaultVal.email : ''}
            className={defaultVal !== '' && defaultVal.email !== '' ? 'focus' : ''}
            label={Drupal.t('Email')}
          />
        </ConditionalView>
        <TextField
          type="tel"
          name="mobile"
          defaultValue={defaultVal !== '' ? cleanMobileNumber(defaultVal.telephone) : ''}
          className={defaultVal !== '' && defaultVal.telephone !== '' ? 'focus' : ''}
          label={Drupal.t('Mobile Number')}
        />
        <input type="hidden" name="address_id" value={defaultVal !== '' && defaultVal.address_id !== null ? defaultVal.address_id : 0} />
      </div>
    </div>
  );
};

export default FixedFields;
