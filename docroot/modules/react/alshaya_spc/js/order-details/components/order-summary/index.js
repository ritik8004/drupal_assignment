import React from 'react';
import parse from 'html-react-parser';
import Aura from '../aura';
import TotalItemCount from '../order-summary-total-item-count';
import CancelledItems from '../order-summary-cancelled-items';

const OrderSummary = (props) => {
  const { order } = props;

  return (
    <div className="order-summary-row">
      <div className="order-transaction">
        <div className="light font-small">{ Drupal.t('Order ID') }</div>
        <div className="dark">{ order.orderId }</div>
        <div className="light font-small">{ order.orderDate }</div>

        <div className="mobile-only">
          <div className="dark">
            { order.name }
            ...
          </div>
          <div className="light">
            <TotalItemCount order={order} />
            <CancelledItems order={order} />
          </div>
        </div>
      </div>

      <div className="above-mobile order-name">
        <div className="dark">
          <div className="dark">
            { order.name }
            ...
          </div>
        </div>
        <div className="light">
          <TotalItemCount order={order} />
          <CancelledItems order={order} />
        </div>

        <div className="tablet-only">
          <div className={`button ${order.status.class}`}>{order.status.text}</div>
        </div>
      </div>

      <div className="mobile-only">
        <div className={`button ${order.status.class}`}>{order.status.text}</div>
      </div>

      <div className="desktop-only order__summary--status">
        <div className={`button ${order.status.class}`}>{order.status.text}</div>
      </div>

      <div className="above-mobile blend order-total">
        <div className="light">{Drupal.t('Order Total')}</div>
        <div className="dark">
          {parse(order.order_details.order_total)}
        </div>
      </div>

      <Aura order={order} />
    </div>
  );
};

export default OrderSummary;
