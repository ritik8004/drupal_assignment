import React from 'react';
import AuraMobileNumberField from '../../../aura-mobile-number-field';
import { getAuraConfig } from '../../../../../../../../alshaya_aura_react/js/utilities/helper';

const LinkCardOptionMobile = (props) => {
  const { setChosenCountryCode, mobile } = props;
  const {
    country_mobile_code: countryMobileCode,
    mobile_maxlength: countryMobileCodeMaxLength,
  } = getAuraConfig();

  return (
    <AuraMobileNumberField
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

export default LinkCardOptionMobile;
