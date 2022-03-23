import React from 'react';
import parse from 'html-react-parser';

const ReturnOrderSummary = ({
  orderDetails,
}) => {
  const itemNames = [];
  Object.keys(orderDetails['#products']).forEach((item) => {
    const itemName = orderDetails['#products'][item].name;
    itemNames.push(itemName);
  });
  return (
    <div className="order-details-wrapper">
      <div className="order-summary-row">
        <div className="order-transaction">
          <div className="light tablet-light">{ Drupal.t('Order ID') }</div>
          <div className="dark">{orderDetails['#order'].orderId}</div>
          <div className="light order--date--time">{ orderDetails['#order'].orderDate }</div>
        </div>
        <div className="order-quantity">
          <div className="dark order--items">{ itemNames.join(',') }</div>
          <div className="light">
            <div className="item-count">
              { Drupal.t('Total:') }
              {itemNames.length}
              {' '}
              { itemNames.length > 1 ? Drupal.t('items') : Drupal.t('item') }
            </div>
          </div>
        </div>
        <div className="order-status">
          <div className="button">{ orderDetails['#order'].status.text }</div>
        </div>
        <div className="order-total-column">
          <div className="order-total-wrapper">
            <div className="light">{ Drupal.t('Order Total') }</div>
            <div className="dark">{parse(orderDetails['#order'].total)}</div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ReturnOrderSummary;
