import React from 'react';
import OnlineBooking from '../../online-booking';
import PriceElement from '../../../../utilities/special-price/PriceElement';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import DefaultShippingElement from './DefaultShippingElement';
import IntercountryTransfer from '../../online-booking/intercountry-transfer';

const ShippingMethodCommon = ({
  cart, refreshCart, method, selected, shippingInfoUpdated,
}) => {
  let price = Drupal.t('FREE');
  if (method.amount > 0) {
    price = <PriceElement amount={method.amount} />;
  }

  // Check if the ict feature is enabled.
  if (selected
    && hasValue(method.extension_attributes)
    && hasValue(method.extension_attributes.ict)) {
    return (
      <IntercountryTransfer ictData={method.extension_attributes.ict} />
    );
  }

  // Check if the order booking feature is enabled.
  if (selected
    && hasValue(method.extension_attributes)
    && hasValue(method.extension_attributes.is_eligible_for_hfd_booking)) {
    return (
      <OnlineBooking
        cart={cart}
        refreshCart={refreshCart}
        price={price}
        method={method}
        shippingInfoUpdated={shippingInfoUpdated}
      />
    );
  }

  return <DefaultShippingElement method={method} price={price} />;
};

export default ShippingMethodCommon;
