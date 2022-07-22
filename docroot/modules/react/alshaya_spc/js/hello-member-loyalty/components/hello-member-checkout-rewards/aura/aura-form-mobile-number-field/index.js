import React from 'react';
import { getAuraConfig } from '../../../../../../../js/utilities/helloMemberHelper';
import AuraMobileNumberFieldDisplay from '../aura-form-mobile-number-field-display';

const AuraFormMobileNumberField = (props) => {
  const { setChosenCountryCode, mobile } = props;
  const {
    country_mobile_code: countryMobileCode,
    mobile_maxlength: countryMobileCodeMaxLength,
  } = getAuraConfig();

  return (
    <AuraMobileNumberFieldDisplay
      isDisabled={false}
      name="spc-aura-link-card-input-mobile"
      countryMobileCode={countryMobileCode}
      maxLength={countryMobileCodeMaxLength}
      setCountryCode={setChosenCountryCode}
      onlyMobileFieldPlaceholder={Drupal.t('Mobile Number')}
      defaultValue={mobile}
    />
  );
};

export default AuraFormMobileNumberField;
