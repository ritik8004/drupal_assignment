import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import OrderItems from '../order-items';

const OrderCancelledItems = (props) => {
  const { order } = props;
  if (!hasValue(order.cancelled_items_count)) {
    return null;
  }

  return (
    <>
      <div id="cancelled-items" className="order-item-row cancelled-items">
        <div>
          <div>{Drupal.t('Cancelled Items')}</div>
        </div>
      </div>
      {hasValue(order.refund_text) && (
        <div className="order-item-row cancelled-refund-text">
          <div>
            <div className="tooltip-anchor cancelled-item-tooltip-refund-text info">
              <span>
                {order.refund_text}
              </span>
            </div>
          </div>
        </div>
      )}
      <OrderItems products={order.cancelled_products} cancelled />
    </>
  );
};

export default OrderCancelledItems;
