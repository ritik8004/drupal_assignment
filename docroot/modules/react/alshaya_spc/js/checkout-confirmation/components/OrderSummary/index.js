import React from 'react';
import moment from 'moment';
import OrderSummaryItem from '../OrderSummaryItem';
import ConditionalView from '../../../common/components/conditional-view';
import OrderSummaryFawryBanner from './order-summary-fawry-banner';
import PriceElement from '../../../utilities/special-price/PriceElement';

const OrderSummary = () => {
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

  let etaLabel = Drupal.t('expected delivery within');
  const storeAddress = [];
  const storeInfo = drupalSettings.order_details.delivery_type_info.store;
  let storePhone = '';
  if (storeInfo !== undefined) {
    storeAddress.push(storeInfo.store_name);
    if (storeInfo.store_address.address_line1 !== undefined
      && storeInfo.store_address.address_line1 !== null) {
      storeAddress.push(storeInfo.store_address.address_line1);
    }
    if (storeInfo.store_address.address_line2 !== undefined
      && storeInfo.store_address.address_line2 !== null) {
      storeAddress.push(storeInfo.store_address.address_line2);
    }
    if (storeInfo.store_address.locality !== undefined
      && storeInfo.store_address.locality !== null) {
      storeAddress.push(storeInfo.store_address.locality);
    }
    if (storeInfo.store_address.dependent_locality !== undefined
      && storeInfo.store_address.dependent_locality !== null) {
      storeAddress.push(storeInfo.store_address.dependent_locality);
    }
    if (storeInfo.store_address.administrative_area_display !== undefined
      && storeInfo.store_address.administrative_area_display !== null) {
      storeAddress.push(storeInfo.store_address.administrative_area_display);
    }
    if (storeInfo.store_address.country !== undefined
      && storeInfo.store_address.country !== null) {
      storeAddress.push(storeInfo.store_address.country);
    }
    if (storeInfo.store_phone !== undefined
      && storeInfo.store_phone !== null) {
      storePhone = storeInfo.store_phone;
    }
    etaLabel = Drupal.t('available instore within');
  }

  const {
    method, transactionId, paymentId, resultCode, bankDetails, orderDate, methodCode,
  } = drupalSettings.order_details.payment;

  // Get Billing info.
  const billingAddress = [];
  let customerNameBilling = '';
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

    customerNameBilling = `${billingInfo.given_name} ${billingInfo.family_name}`;
  }

  // Customer name on shipping.
  const customerShippingName = drupalSettings.order_details.delivery_type_info.customerNameShipping;

  // Fawry details.
  // Fawry details.
  const {
    payment: {
      referenceNumber,
      paymentExpiryTime,
    },
    totals: {
      base_grand_total: baseGrandTotal,
    },
  } = drupalSettings.order_details;
  const priceTotal = <PriceElement amount={baseGrandTotal} />;

  return (
    <div className="spc-order-summary">
      <div className="spc-order-summary-order-preview">
        <ConditionalView condition={methodCode !== undefined && methodCode === 'checkout_com_upapi_fawry'}>
          <OrderSummaryFawryBanner animationDelay="0.5s" />
          <OrderSummaryItem animationDelay="0.5s" label={Drupal.t('Amount Due')} value={priceTotal} />
          <OrderSummaryItem animationDelay="0.5s" label={Drupal.t('Reference number')} value={referenceNumber} />
          <OrderSummaryItem animationDelay="0.5s" label={Drupal.t('Complete payment by')} value={moment(paymentExpiryTime).format('DD MMMM YYYY, HH:mm a')} />
        </ConditionalView>
        <OrderSummaryItem animationDelay="0.5s" label={Drupal.t('confirmation email sent to')} value={customEmail} />
        <OrderSummaryItem animationDelay="0.6s" label={Drupal.t('order number')} value={orderNumber} />

        <ConditionalView condition={transactionId !== undefined && transactionId !== null}>
          <OrderSummaryItem animationDelay="0.7s" label={Drupal.t('Transaction ID')} value={transactionId} />
        </ConditionalView>
        <ConditionalView condition={paymentId !== undefined && paymentId !== null}>
          <OrderSummaryItem animationDelay="0.7s" label={Drupal.t('Payment ID')} value={paymentId} />
        </ConditionalView>
        <ConditionalView condition={resultCode !== undefined && resultCode !== null}>
          <OrderSummaryItem animationDelay="0.7s" label={Drupal.t('Result code')} value={resultCode} />
        </ConditionalView>
        <ConditionalView condition={orderDate !== undefined && orderDate !== null}>
          <OrderSummaryItem animationDelay="0.7s" label={Drupal.t('Date')} value={orderDate} />
        </ConditionalView>
        <ConditionalView condition={bankDetails !== undefined && bankDetails !== null}>
          <OrderSummaryItem
            type="markup"
            label={Drupal.t('Bank details')}
            value={bankDetails}
            animationDelay="0.7s"
          />
        </ConditionalView>
      </div>
      <div className="spc-order-summary-order-detail fadeInUp" style={{ animationDelay: '0.8s' }}>
        <input type="checkbox" id="spc-detail-open" />
        <label htmlFor="spc-detail-open">{Drupal.t('order detail')}</label>
        <div className="spc-detail-content">
          <ConditionalView condition={customerAddress.length > 0}>
            <OrderSummaryItem type="address" label={Drupal.t('delivery to')} name={customerShippingName} address={customerAddress.join(', ')} />
          </ConditionalView>
          {(storeAddress.length > 0 && storeInfo !== undefined)
            && (
              <>
                <OrderSummaryItem
                  type="click_and_collect"
                  label={Drupal.t('Collection Store')}
                  name={storeInfo.store_name}
                  phone={storePhone}
                  address={storeAddress.join(', ')}
                  openingHours={storeInfo.store_open_hours}
                  mapLink={storeInfo.view_on_map_link}
                />
                <OrderSummaryItem label={Drupal.t('Collection by')} value={customerShippingName} />
              </>
            )}
          <ConditionalView condition={billingAddress.length > 0}>
            <OrderSummaryItem type="address" label={Drupal.t('Billing address')} name={customerNameBilling} address={billingAddress.join(', ')} />
          </ConditionalView>
          <OrderSummaryItem type="mobile" label={Drupal.t('Mobile Number')} value={mobileNumber} />
          <OrderSummaryItem label={Drupal.t('Payment method')} value={method} />
          <OrderSummaryItem label={Drupal.t('delivery type')} value={deliveryType} />
          <OrderSummaryItem label={etaLabel} value={expectedDelivery} />
          <OrderSummaryItem label={Drupal.t('number of items')} value={itemsCount} />
        </div>
      </div>
    </div>
  );
};

export default OrderSummary;
