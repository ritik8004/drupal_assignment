import React from 'react';

import SectionTitle from '../../../utilities/section-title';
import TextField from '../../../utilities/textfield';
import ConditionalView from '../../../common/components/conditional-view';
import {
  cleanMobileNumber,
  isFieldDisabled,
} from '../../../utilities/checkout_util';
import getStringMessage from '../../../utilities/strings';
import collectionPointsEnabled from '../../../../../js/utilities/pudoAramaxCollection';

const FixedFields = ({
  defaultVal,
  showEmail,
  showFullName = true,
  subTitle,
  type,
  enabledFieldsWithMessages,
}) => {
  let defaultValue = '';
  // Adding check for static fields when pre-populating form
  // with default address values.
  if (defaultVal.length !== 0 && defaultVal.length !== 'undefined' && defaultVal.static !== undefined) {
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
          {collectionPointsEnabled() === false
            && (
              <span className="spc-contact-info-desc">{subTitle}</span>
            )}
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
            // The prop enabledFieldsWithMessages has fieldname and default
            // message to show with the field
            // example {mobile: 'Please update mobile number'}
            // if fullname is not present then it will be disabled.
            disabled={isFieldDisabled(enabledFieldsWithMessages, 'fullname')}
          />
        </ConditionalView>
        <ConditionalView condition={showEmail}>
          <TextField
            type="email"
            name="email"
            defaultValue={defaultValue !== '' ? defaultValue.email : ''}
            className={defaultValue !== '' && defaultValue.email !== '' ? 'focus' : ''}
            label={getStringMessage('ci_email')}
            // The prop enabledFieldsWithMessages has fieldname and default
            // message to show with the field
            // example {mobile: 'Please update mobile number'}
            // if fullname is not present then it will be disabled.
            disabled={isFieldDisabled(enabledFieldsWithMessages, 'email')}
          />
        </ConditionalView>
        <TextField
          type="tel"
          name="mobile"
          defaultValue={defaultValue !== '' ? cleanMobileNumber(defaultValue.telephone) : ''}
          className={defaultValue !== '' && defaultValue.telephone !== '' ? 'focus' : ''}
          label={getStringMessage('ci_mobile_number')}
          disabled={isFieldDisabled(enabledFieldsWithMessages, 'mobile')}
          // This prop is an object where object keys are field-names which will
          // be enabled in the form and values are default message on the field
          // example {mobile: Please update mobile number}
          enabledFieldsWithMessages={enabledFieldsWithMessages}
        />
        <input type="hidden" name="address_id" value={defaultValue !== '' && defaultValue.address_id !== null ? defaultValue.address_id : 0} />
      </div>
    </div>
  );
};

export default FixedFields;
