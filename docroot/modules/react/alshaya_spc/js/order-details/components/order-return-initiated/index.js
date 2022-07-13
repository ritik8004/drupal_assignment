import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { isOnlineReturnsEnabled } from '../../../../../js/utilities/onlineReturnsHelper';
import ReturnInitiated from '../../../../../alshaya_online_returns/js/order-details/return-initiated';

const OrderReturnInitiated = (props) => {
  const { returns, order, handleErrorMessage } = props;
  if (!isOnlineReturnsEnabled() || !hasValue(order.online_returns_status)) {
    return null;
  }

  return (
    <>
      <ReturnInitiated returns={returns} handleErrorMessage={handleErrorMessage} />
      { hasValue(order.products) && (
        <div className="order-item-row delivered-items">
          <div>
            <div>{Drupal.t('Delivered Items')}</div>
          </div>
        </div>
      )}
    </>
  );
};

export default OrderReturnInitiated;
