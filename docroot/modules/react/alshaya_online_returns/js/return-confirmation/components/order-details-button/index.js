import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

const OrderDetailsButton = ({
  orderId,
}) => {
  if (orderId && hasValue(drupalSettings.user)
    && drupalSettings.user.isCustomer) {
    const { uid } = drupalSettings.user;
    return (
      <>
        <div className="order-details-button-wrapper">
          <a
            href={Drupal.url(`user/${uid}/order/${orderId}`)}
            className="order-detail"
          >
            {Drupal.t('Go to order details', {}, { context: 'online_returns' })}
          </a>
        </div>
      </>
    );
  }
  return null;
};

export default OrderDetailsButton;
