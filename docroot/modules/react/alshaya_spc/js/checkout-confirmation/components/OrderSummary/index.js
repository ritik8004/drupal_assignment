import React from 'react';
import OrderSummaryItem from '../OrderSummaryItem';
import ConditionalView from '../../../common/components/conditional-view';

class OrderSummary extends React.Component {
  constructor(props) {
    super(props);
    this.orderDetails = drupalSettings.order_details;
  }

  render = () => {
    const customerName = this.orderDetails.customer_name;
    const customEmail = this.orderDetails.customer_email;
    const orderNumber = this.orderDetails.order_number;
    const mobileNumber = this.orderDetails.mobile_number;
    const paymentMethod = this.orderDetails.payment_method;
    const deliveryType = this.orderDetails.delivery_type_info.type;
    const expectedDelivery = this.orderDetails.expected_delivery;
    const itemsCount = this.orderDetails.number_of_items;

    const addressLine1 = this.orderDetails.delivery_type_info.delivery_address.address_line1;
    const addressLine2 = this.orderDetails.delivery_type_info.delivery_address.address_line2;
    const {
      country,
      locality,
      dependent_locality: dependentLocality,
    } = this.orderDetails.delivery_type_info.delivery_address;
    const customerAddress = ` ${country}, ${addressLine1}, ${addressLine2}, ${locality}, ${dependentLocality}`;

    const {
      transactionId, paymentId, resultCode, bankDetails,
    } = this.orderDetails.payment;

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
          <ConditionalView condition={bankDetails !== undefined && bankDetails !== null}>
            <OrderSummaryItem label={Drupal.t('Bank details')} value={bankDetails} />
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
  };
}

export default OrderSummary;
