import React from 'react';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

const ReturnCollectionAddress = ({
  shippingAddress,
}) => {
  if (!hasValue(drupalSettings.address_fields) || !hasValue(shippingAddress)) {
    return null;
  }

  const addressData = [];
  // Add country label to address item array.
  if (hasValue(shippingAddress.country_label)) {
    addressData.push(shippingAddress.country_label);
  }
  // Populate address field with each key item.
  Object.keys(drupalSettings.address_fields).forEach((key) => {
    if (hasValue(shippingAddress[key])) {
      let fillVal = shippingAddress[key];
      if (key === 'administrative_area') {
        fillVal = shippingAddress.administrative_area_display;
      } else if (key === 'area_parent') {
        fillVal = shippingAddress.area_parent_display;
      }
      addressData.push(fillVal);
    }
  });

  return (
    <>
      <div className="return-address-wrapper">
        <div className="return-address-title">
          { Drupal.t('Pick-up Address', {}, { context: 'online_returns' }) }
        </div>
        <div className="return-address-desc">
          { `(${Drupal.t('Last used address and phone number will be applied', {}, { context: 'online_returns' })})` }
        </div>
        <div className="return-address-details">
          <ConditionalView condition={hasValue(shippingAddress.given_name)}>
            <div className="customer-name">
              { Drupal.t('@first_name: @last_name', { '@first_name': shippingAddress.given_name, '@last_name': shippingAddress.family_name }, {}, { context: 'online_returns' }) }
            </div>
          </ConditionalView>
          {addressData.length > 0 && addressData.map((adressItem) => (
            <div key={adressItem} className="address-line-content">{adressItem}</div>
          ))}
          <div className="spc-phone-number">{shippingAddress.telephone}</div>
        </div>
      </div>
    </>
  );
};

export default ReturnCollectionAddress;
