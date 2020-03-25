import React from 'react';
import OrderSummaryItem from '../OrderSummaryItem';
import ConditionalView from '../../../common/components/conditional-view';

const OrderSummary = () => {
  const customerName = drupalSettings.order_details.customer_name;
  const customEmail = drupalSettings.order_details.customer_email;
  const orderNumber = drupalSettings.order_details.order_number;
  const mobileNumber = drupalSettings.order_details.mobile_number;
  const deliveryType = drupalSettings.order_details.delivery_type_info.type;
  const expectedDelivery = drupalSettings.order_details.expected_delivery;
  const itemsCount = drupalSettings.order_details.number_of_items;

  const customerAddress = [];
  const addressInfo = drupalSettings.order_details.delivery_type_info.delivery_address;
  if (addressInfo !== undefined) {
    customerAddress.push(addressInfo.country);
    if (addressInfo.address_line1 !== undefined) {
      customerAddress.push(addressInfo.address_line1);
    }
    if (addressInfo.address_line2 !== undefined) {
      customerAddress.push(addressInfo.address_line2);
    }
    if (addressInfo.administrative_area_display !== undefined) {
      customerAddress.push(addressInfo.administrative_area_display);
    }
    if (addressInfo.dependent_locality !== undefined) {
      customerAddress.push(addressInfo.dependent_locality);
    }
  }

  const storeAddress = [];
  let storeHours = [];
  const storeInfo = drupalSettings.order_details.delivery_type_info.store;
  if (storeInfo !== undefined) {
    storeAddress.push(storeInfo.store_name);
    storeAddress.push(storeInfo.store_address.address_line1);
    storeAddress.push(storeInfo.store_address.address_line2);
    storeAddress.push(storeInfo.store_address.locality);
    storeAddress.push(storeInfo.store_address.dependent_locality);
    storeAddress.push(storeInfo.store_address.administrative_area_display);
    storeAddress.push(storeInfo.store_address.country);
    storeAddress.push(storeInfo.store_phone);
    storeHours = drupalSettings.order_details.delivery_type_info.store.store_open_hours;
  }

  const {
    method, transactionId, paymentId, resultCode, bankDetails,
  } = drupalSettings.order_details.payment;

  // Get Billing info.
  const billingAddress = [];
  const billingInfo = drupalSettings.order_details.billing;
  if (billingInfo !== null) {
    billingAddress.push(billingInfo.country);
    if (billingInfo.area_parent_display !== undefined) {
      billingAddress.push(billingInfo.area_parent_display);
    }
    if (billingInfo.administrative_area_display !== undefined) {
      billingAddress.push(billingInfo.administrative_area_display);
    }
    if (billingInfo.address_line1 !== undefined) {
      billingAddress.push(billingInfo.address_line1);
    }
    if (billingInfo.address_line2 !== undefined) {
      billingAddress.push(billingInfo.address_line2);
    }
    if (billingInfo.dependent_locality !== undefined) {
      billingAddress.push(billingInfo.dependent_locality);
    }
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
          <OrderSummaryItem
            type="markup"
            label={Drupal.t('Bank details')}
            value={bankDetails}
          />
        </ConditionalView>
      </div>
      <div className="spc-order-summary-order-detail">
        <input type="checkbox" id="spc-detail-open" />
        <label htmlFor="spc-detail-open">{Drupal.t('order detail')}</label>
        <div className="spc-detail-content">
          <ConditionalView condition={customerAddress.length > 0}>
            <OrderSummaryItem type="address" label={Drupal.t('delivery to')} name={customerName} address={customerAddress.join(', ')} />
          </ConditionalView>
          <ConditionalView condition={storeAddress.length > 0}>
            <OrderSummaryItem type="cnc" label={Drupal.t('delivery to')} name={customerName} address={storeAddress.join(', ')} timings={storeHours.join('\n')} />
          </ConditionalView>
          <ConditionalView condition={billingAddress.length > 0}>
            <OrderSummaryItem type="address" label={Drupal.t('billing address')} name={customerName} address={billingAddress.join(', ')} />
          </ConditionalView>
          <OrderSummaryItem label={Drupal.t('mobile number')} value={mobileNumber} />
          <OrderSummaryItem label={Drupal.t('payment method')} value={method} />
          <OrderSummaryItem label={Drupal.t('delivery type')} value={deliveryType} />
          <OrderSummaryItem label={Drupal.t('expected delivery within')} value={expectedDelivery} />
          <OrderSummaryItem label={Drupal.t('number of items')} value={itemsCount} />
        </div>
      </div>
    </div>
  );
};

export default OrderSummary;
