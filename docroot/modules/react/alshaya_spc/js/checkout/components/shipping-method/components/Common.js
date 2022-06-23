import React from 'react';
import OnlineBooking from '../../online-booking';
import PriceElement from '../../../../utilities/special-price/PriceElement';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import DefaultShippingElement from './DefaultShippingElement';
import IctDeliveryInformation from '../../online-booking/ict-delivery-information';

const ShippingMethodCommon = ({
  cart, refreshCart, method, selected, shippingInfoUpdated,
}) => {
  let price = Drupal.t('FREE');
  if (method.amount > 0) {
    price = <PriceElement amount={method.amount} />;
  }

  // Check if the inter country feature is enabled.
  // return ict component if available.
  if (selected
    && hasValue(method.extension_attributes)
    && hasValue(method.extension_attributes.oms_lead_time)) {
    const ictDate = method.extension_attributes.oms_lead_time;
    return (
      <IctDeliveryInformation deliveryMethod="home_delivery" date={ictDate} />
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
