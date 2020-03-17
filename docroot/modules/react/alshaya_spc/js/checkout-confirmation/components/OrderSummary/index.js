import React from 'react';
import OrderSummaryItem from '../OrderSummaryItem';
import ConditionalView from '../../../common/components/conditional-view';

export default class OrderSummary extends React.Component {
  render() {
    const customerName = drupalSettings.order_details.customer_name;
    const customEmail = drupalSettings.order_details.customer_email;
    const orderNumber = drupalSettings.order_details.order_number;
    const transactionId = drupalSettings.order_details.transaction_id;
    const paymentId = drupalSettings.order_details.payment_id;
    const resultCode = drupalSettings.order_details.result_code;
    const addressLine1 = drupalSettings.order_details.delivery_type_info.delivery_address.address_line1;
    const addressLine2 = drupalSettings.order_details.delivery_type_info.delivery_address.address_line2;
    const { locality } = drupalSettings.order_details.delivery_type_info.delivery_address;
    const { country } = drupalSettings.order_details.delivery_type_info.delivery_address;
    const dependentLocality = drupalSettings.order_details.delivery_type_info.delivery_address.dependent_locality;
    const customerAddress = ` ${country}, ${addressLine1}, ${addressLine2}, ${locality}, ${dependentLocality}`;
    const mobileNumber = drupalSettings.order_details.mobile_number;
    const paymentMethod = drupalSettings.order_details.payment_method;
    const deliveryType = drupalSettings.order_details.delivery_type_info.type;
    const expectedDelivery = drupalSettings.order_details.expected_delivery;
    const itemsCount = drupalSettings.order_details.number_of_items;
    return (
      <div className="spc-order-summary">
        <div className="spc-order-summary-order-preview">
          <OrderSummaryItem label={Drupal.t('confirmation email sent to')} value={customEmail} />
          <OrderSummaryItem label={Drupal.t('order number')} value={orderNumber} />

          <ConditionalView condition={transactionId !== undefined && transactionId !== null}>
            <OrderSummaryItem label={Drupal.t('Transaction ID')} value={transactionId} />
          </ConditionalView>
          <ConditionalView condition={paymentId !== undefined && paymentId !== null}>
            <OrderSummaryItem label={Drupal.t('Payment ID')} value={paymentId} />
          </ConditionalView>
          <ConditionalView condition={resultCode !== undefined && resultCode !== null}>
            <OrderSummaryItem label={Drupal.t('Result code')} value={resultCode} />
          </ConditionalView>
        </div>
        <div className="spc-order-summary-order-detail">
          <input type="checkbox" id="spc-detail-open" />
          <label htmlFor="spc-detail-open">{Drupal.t('order detail')}</label>
          <div className="spc-detail-content">
            <OrderSummaryItem type="address" label={Drupal.t('delivery to')} name={customerName} address={customerAddress} />
            <OrderSummaryItem label={Drupal.t('mobile number')} value={mobileNumber} />
            <OrderSummaryItem label={Drupal.t('payment method')} value={paymentMethod} />
            <OrderSummaryItem label={Drupal.t('delivery type')} value={deliveryType} />
            <OrderSummaryItem label={Drupal.t('expected delivery within')} value={expectedDelivery} />
            <OrderSummaryItem label={Drupal.t('number of items')} value={itemsCount} />
          </div>
        </div>
      </div>
    );
  }
}
