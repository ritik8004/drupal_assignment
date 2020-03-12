import React from 'react';
import OrderSummaryItem from '../OrderSummaryItem';

export default class OrderSummary extends React.Component {
  render() {
    const customerName = window.drupalSettings.order_details.customer_name;
    const customEmail = window.drupalSettings.customer_email;
    const orderNumber = window.drupalSettings.order_number;
    const transactionId = window.drupalSettings.transaction_id;
    const addressLine1 = window.drupalSettings.order_details.shipping_address.address_line1;
    const addressLine2 = window.drupalSettings.order_details.shipping_address.address_line2;
    const locality = window.drupalSettings.order_details.shipping_address.locality;
    const dependentLocality = window.drupalSettings.order_details.shipping_address.dependent_locality;
    const customerAddress = addressLine1 + ',' + addressLine2 + ',' + locality + ',' + dependentLocality;
    const mobileNumber = window.drupalSettings.order_details.mobile_number;
    const paymentMethod = window.drupalSettings.order_details.payment_method;
    const deliveryType = window.drupalSettings.order_details.delivery_type;
    const expectedDelivery = window.drupalSettings.order_details.expected_delivery;
    const itemsCount = window.drupalSettings.order_details.number_of_items
    return (
      <div className="spc-order-summary">
        <div className="spc-order-summary-order-preview">
          <OrderSummaryItem label={Drupal.t('Corfimation email sent to')} value={customEmail} />
          <OrderSummaryItem label={Drupal.t('Order number')} value={orderNumber} />
          <OrderSummaryItem label={Drupal.t('Transaction ID')} value={transactionId} />
        </div>
        <div className="spc-order-summary-order-detail">
          <input type="checkbox" id="spc-detail-open" />
          <label htmlFor="spc-detail-open">{Drupal.t('Order Detail')}</label>
          <div className="spc-detail-content">
            <OrderSummaryItem type="address" label={Drupal.t('Delivery to')} name={customerName} address={customerAddress} />
            <OrderSummaryItem label={Drupal.t('Mobile Number')} value={mobileNumber} />
            <OrderSummaryItem label={Drupal.t('Payment method')} value={paymentMethod} />
            <OrderSummaryItem label={Drupal.t('Delivery type')} value={deliveryType} />
            <OrderSummaryItem label={Drupal.t('Expected delivery within')} value={expectedDelivery} />
            <OrderSummaryItem label={Drupal.t('Number of items')} value={itemsCount} />
          </div>
        </div>
      </div>
    );
  }
}
