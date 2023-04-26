import React from 'react';
import CheckoutConfigurableOption from '../checkout-configurable-option';
import { hasValue } from '../../../../js/utilities/conditionsUtility';

const CheckoutConfigurableOptions = (props) => {
  const { options, sku, sizeGroup } = props;
  const configurableOptions = [];
  options.forEach((option) => {
    const configurableOption = option;
    // Override option value if size group exists for the item attibute.
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
      {/* Iterate over and render all configurable attributes */}
      {configurableOptions.map((key) => <CheckoutConfigurableOption key={`${sku}-${key.value}`} label={key} />)}
    </>
  );
};

export default CheckoutConfigurableOptions;
