import React from 'react';
import CheckoutConfigurableOption from '../checkout-configurable-option';
import { hasValue } from '../../../../js/utilities/conditionsUtility';

const CheckoutConfigurableOptions = (props) => {
  const { options, sku, sizeGroup } = props;
  // Override value if size group exists.
  const configurableOptions = [];
  options.forEach((option) => {
    const configurableOption = option;
    configurableOption.value = (
      option.attribute_code === `attr_${drupalSettings.alshaya_spc.sizeGroupAttribute}`
      && hasValue(sizeGroup)
    )
      ? sizeGroup
      : option.value;
    configurableOptions.push(configurableOption);
  });

  return (
    <>
      {configurableOptions.map((key) => <CheckoutConfigurableOption key={`${sku}-${key.value}`} label={key} />)}
    </>
  );
};

export default CheckoutConfigurableOptions;
