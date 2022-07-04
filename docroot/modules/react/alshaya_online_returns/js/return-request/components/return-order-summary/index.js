import React from 'react';
import parse from 'html-react-parser';

const ReturnOrderSummary = ({
  orderDetails,
}) => {
  const itemNames = [];
  Object.keys(orderDetails['#products']).forEach((item) => {
    const itemName = orderDetails['#products'][item].name;
    // Consider only the valid product items where ordered qty is more then 0.
    if (orderDetails['#products'][item].qty_ordered > 0) {
      itemNames.push(itemName);
    }
  });
  return (
    <div className="order-details-wrapper">
      <div className="order-summary-row">
        <div className="order-summary-details">
          <div className="order-transaction">
            <div className="light tablet-light font-small">{ Drupal.t('Order ID', {}, { context: 'online_returns' }) }</div>
            <div className="dark">{orderDetails['#order'].orderId}</div>
            <div className="light order--date--time font-small">{ orderDetails['#order'].orderDate }</div>
          </div>
          <div className="order-quantity">
            <div className="dark order--items">{ itemNames.join(',') }</div>
            <div className="light">
              <div className="item-count">
                { itemNames.length === 1
                  ? Drupal.t('Total: 1 item')
                  : Drupal.t('Total: @count items', { '@count': itemNames.length }) }
              </div>
            </div>
          </div>
        </div>
        <div className="order-status">
          <div className={`button ${orderDetails['#order'].status.class}`}>{ orderDetails['#order'].status.text }</div>
        </div>
        <div className="order-total-column">
          <div className="order-total-wrapper">
            <div className="light">{ Drupal.t('Order Total', {}, { context: 'online_returns' }) }</div>
            <div className="dark">{parse(orderDetails['#order'].total)}</div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ReturnOrderSummary;
