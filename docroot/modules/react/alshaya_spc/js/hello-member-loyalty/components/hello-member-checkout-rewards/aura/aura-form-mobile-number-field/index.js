import React from 'react';
import { hasValue } from '../../../../../../../js/utilities/conditionsUtility';
import { getAuraFormConfig } from '../../../../../../../js/utilities/helloMemberHelper';
import getStringMessage from '../../../../../../../js/utilities/strings';
import AuraMobileNumberFieldDisplay from '../aura-form-mobile-number-field-display';

const AuraFormMobileNumberField = (props) => {
  const { setChosenCountryCode, mobile } = props;
  const auraConfig = getAuraFormConfig();
  if (!hasValue(auraConfig)) {
    return null;
  }
  const {
    country_mobile_code: countryMobileCode,
    mobile_maxlength: countryMobileCodeMaxLength,
  } = auraConfig;

  return (
    <AuraMobileNumberFieldDisplay
      isDisabled={false}
      name="spc-aura-link-card-input-mobile"
      countryMobileCode={countryMobileCode}
      maxLength={countryMobileCodeMaxLength}
      setCountryCode={setChosenCountryCode}
      onlyMobileFieldPlaceholder={getStringMessage('mobile_number')}
      defaultValue={mobile}
    />
  );
};

export default AuraFormMobileNumberField;
