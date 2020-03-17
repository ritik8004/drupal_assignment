import React from 'react';
import OrderSummaryItem from '../OrderSummaryItem';
import ConditionalView from '../../../common/components/conditional-view';

class OrderSummary extends React.Component {
  constructor(props) {
    super(props);
  }

  render() {
    const customerName = drupalSettings.order_details.customer_name;
    const customEmail = drupalSettings.order_details.customer_email;
    const orderNumber = drupalSettings.order_details.order_number;
    const mobileNumber = drupalSettings.order_details.mobile_number;
    const paymentMethod = drupalSettings.order_details.payment_method;
    const deliveryType = drupalSettings.order_details.delivery_type_info.type;
    const expectedDelivery = drupalSettings.order_details.expected_delivery;
    const itemsCount = drupalSettings.order_details.number_of_items;

    const {
      country,
      administrative_area_display: locality,
      dependent_locality: dependentLocality,
      address_line1: addressLine1,
      address_line2: addressLine2,
    } = drupalSettings.order_details.delivery_type_info.delivery_address;
    const customerAddress = ` ${country}, ${addressLine1}, ${addressLine2}, ${locality}, ${dependentLocality}`;

    const {
      transactionId, paymentId, resultCode, bankDetails,
    } = drupalSettings.order_details.payment;

    // Get Billing info.
    const paymentMethodCode = drupalSettings.order_details.payment_method_code;
    var billingAddress = '';
    if (paymentMethodCode !== 'cashondelivery') {
      const area_parent_display = drupalSettings.order_details.billing_info.area_parent_display;
      const admin_area_display = drupalSettings.order_details.billing_info.administrative_area_display;
      const billing_addressLine1 = drupalSettings.order_details.billing_info.address_line1;
      const billing_addressLine2 = drupalSettings.order_details.billing_info.address_line2;
      const billing_localty = drupalSettings.order_details.billing_info.dependent_locality;
      billingAddress = ' ' + country + ', ' + area_parent_display + ', ' + admin_area_display + ', ' + billing_addressLine1 + ', ' + billing_addressLine2 + ', ' + billing_localty;
    }


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
            <ConditionalView condition={paymentMethodCode !== 'cashondelivery'}>
              <OrderSummaryItem type="address" label={Drupal.t('billing address')} name={customerName} address={billingAddress} />
            </ConditionalView>
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
