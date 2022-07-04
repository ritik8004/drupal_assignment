import React from 'react';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { getAdressData } from '../../../utilities/online_returns_util';

const ReturnConfirmationAddress = ({
  shippingAddress,
}) => {
  const addressData = getAdressData(shippingAddress);

  if (!hasValue(addressData)) {
    return null;
  }

  return (
    <>
      <div className="return-confirmation-address">
        <div className="address-title light">
          { Drupal.t('Pick-up From', {}, { context: 'online_returns' }) }
        </div>
        <div className="address-details">
          <ConditionalView condition={hasValue(shippingAddress.given_name)}>
            <div className="customer-name">
              {shippingAddress.given_name}
              {' '}
              {shippingAddress.family_name}
            </div>
          </ConditionalView>
          {hasValue(addressData) && addressData.map((adressItem) => (
            <div key={adressItem} className="address-line-content">{adressItem}</div>
          ))}
          <div className="phone-number">{shippingAddress.telephone}</div>
        </div>
      </div>
    </>
  );
};

export default ReturnConfirmationAddress;
