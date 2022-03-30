import React from 'react';
import moment from 'moment';
import parse from 'html-react-parser';
import OrderSummaryItem from '../OrderSummaryItem';
import ConditionalView from '../../../common/components/conditional-view';
import AuraEarnOrderSummaryItem
  from '../../../aura-loyalty/components/aura-earn-order-summary-item';
import AuraRedeemOrderSummaryItem
  from '../../../aura-loyalty/components/aura-redeem-order-summary-item';
import isAuraEnabled from '../../../../../js/utilities/helper';
import OrderSummaryFawryBanner from './order-summary-fawry-banner';
import PriceElement from '../../../utilities/special-price/PriceElement';
import getStringMessage from '../../../utilities/strings';
import collectionPointsEnabled from '../../../../../js/utilities/pudoAramaxCollection';
import PaymentMethodIcon from '../../../svg-component/payment-method-svg';
import { isEgiftCardEnabled } from '../../../../../js/utilities/util';
import EgiftOrderSummaryItem from '../../../egift-card/components/egift-order-summary-item';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

const OrderSummary = (props) => {
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
    if (addressInfo.postal_code !== undefined) {
      customerAddress.push(addressInfo.postal_code);
    }
  }

  let etaLabel = Drupal.t('expected delivery within');
  let methodIcon = '';
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
    etaLabel = (collectionPointsEnabled() && storeInfo.pudo_available === true)
      ? Drupal.t('available in collection point within')
      : Drupal.t('available instore within');
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

  const {
    accruedPoints, redeemedPoints,
  } = drupalSettings.order_details;

  const { context, loyaltyStatus } = props;
  // Fawry details.
  const {
    payment: {
      referenceNumber,
      paymentExpiryTime,
    },
    totals: {
      base_grand_total: baseGrandTotal,
    },
    delivery_type_info: {
      collection_date: collectionDate,
      collection_charge: collectionCharge,
    },
  } = drupalSettings.order_details;
  const priceTotal = <PriceElement amount={baseGrandTotal} />;

  // Set language for datetime translation.
  const { currentLanguage } = drupalSettings.path;
  if (currentLanguage !== undefined) {
    moment.locale(currentLanguage);
  }

  if (methodCode === 'tabby') {
    methodIcon = <PaymentMethodIcon methodName={methodCode} methodLabel={method} />;
  }
  // Dont show Delivery related summary if order has only virtual product,
  // i.e Egift card or Egift Topup.
  let showDeliverySummary = true;
  if (isEgiftCardEnabled() && drupalSettings.order_details.isOnlyVirtualProduct) {
    showDeliverySummary = false;
  }

  // Define the styles based on component context.
  let styles = {
    animationDelay: '0.8s',
  };
  if (context === 'print') {
    styles = {
      animation: 'none !important',
      transition: 'none !important',
    };
  }

  // Get the HFD order booing informaion from the drupal settings if available.
  const onliineBookingInformation = hasValue(
    drupalSettings.order_details.onliineBookingInformation,
  )
    ? drupalSettings.order_details.onliineBookingInformation
    : false;

  return (
    <div className="spc-order-summary">
      <div className="spc-order-summary-order-preview">
        <ConditionalView condition={methodCode !== undefined && methodCode === 'checkout_com_upapi_fawry'}>
          <OrderSummaryFawryBanner animationDelay="0.5s" />
          <OrderSummaryItem context={context} animationDelay="0.5s" label={getStringMessage('fawry_amount_due')} value={priceTotal} />
          <OrderSummaryItem context={context} animationDelay="0.5s" label={getStringMessage('fawry_reference_number')} value={referenceNumber} />
          <OrderSummaryItem context={context} animationDelay="0.5s" label={getStringMessage('fawry_complete_payment_by')} value={moment(paymentExpiryTime).format('DD MMMM YYYY, HH:mm a')} />
        </ConditionalView>
        <OrderSummaryItem context={context} animationDelay="0.5s" label={Drupal.t('confirmation email sent to')} value={customEmail} />
        <OrderSummaryItem context={context} animationDelay="0.6s" label={Drupal.t('order number')} value={orderNumber} />

        <ConditionalView condition={transactionId !== undefined && transactionId !== null}>
          <OrderSummaryItem context={context} animationDelay="0.7s" label={Drupal.t('Transaction ID')} value={transactionId} />
        </ConditionalView>
        <ConditionalView condition={paymentId !== undefined && paymentId !== null}>
          <OrderSummaryItem context={context} animationDelay="0.7s" label={Drupal.t('Payment ID')} value={paymentId} />
        </ConditionalView>
        <ConditionalView condition={resultCode !== undefined && resultCode !== null}>
          <OrderSummaryItem context={context} animationDelay="0.7s" label={Drupal.t('Result code')} value={resultCode} />
        </ConditionalView>
        <ConditionalView condition={orderDate !== undefined && orderDate !== null}>
          <OrderSummaryItem context={context} animationDelay="0.7s" label={Drupal.t('Date')} value={orderDate} />
        </ConditionalView>
        <ConditionalView condition={bankDetails !== undefined && bankDetails !== null}>
          <OrderSummaryItem
            type="markup"
            label={Drupal.t('Bank details')}
            value={bankDetails}
            animationDelay="0.7s"
            context={context}
          />
        </ConditionalView>
      </div>
      <div className="spc-order-summary-order-detail fadeInUp" style={styles}>
        <input type="checkbox" id="spc-detail-open" />
        <label htmlFor="spc-detail-open">{Drupal.t('order detail')}</label>
        <div className="spc-detail-content">
          <ConditionalView condition={customerAddress.length > 0 && showDeliverySummary}>
            <OrderSummaryItem context={context} type="address" label={Drupal.t('delivery to')} name={customerShippingName} address={customerAddress.join(', ')} />
          </ConditionalView>
          {(storeAddress.length > 0 && storeInfo !== undefined)
            && (
              <>
                <OrderSummaryItem
                  type="click_and_collect"
                  label={Drupal.t('Collection Store')}
                  name={storeInfo.store_name}
                  phone={storePhone}
                  context={context}
                  address={storeAddress.join(', ')}
                  openingHours={storeInfo.store_open_hours}
                  mapLink={storeInfo.view_on_map_link}
                  {...(collectionPointsEnabled() && storeInfo.pudo_available !== undefined
                    && { pickUpPointIcon: storeInfo.pudo_available ? 'collection-point' : 'store' })}
                  {...(collectionPointsEnabled() && storeInfo.collection_point !== undefined
                    && { pickUpPointTitle: storeInfo.collection_point })}
                  {...(collectionPointsEnabled() && collectionDate !== undefined
                    && { collectionDate })}
                  {...(collectionPointsEnabled() && collectionCharge !== undefined
                    && { collectionCharge })}
                />
                <OrderSummaryItem context={context} label={Drupal.t('Collection by')} value={customerShippingName} />
              </>
            )}
          <ConditionalView condition={billingAddress.length > 0}>
            <OrderSummaryItem context={context} type="address" label={Drupal.t('Billing address')} name={customerNameBilling} address={billingAddress.join(', ')} />
          </ConditionalView>
          <ConditionalView condition={isEgiftCardEnabled()}>
            <EgiftOrderSummaryItem
              orderDetails={drupalSettings.order_details}
              context={context}
            />
          </ConditionalView>
          <OrderSummaryItem context={context} type="mobile" label={Drupal.t('Mobile Number')} value={mobileNumber} />
          <OrderSummaryItem context={context} label={Drupal.t('Payment method')} value={methodIcon || method} />
          <ConditionalView condition={showDeliverySummary}>
            <OrderSummaryItem context={context} label={Drupal.t('delivery type')} value={deliveryType} />
            {/** Show HFD booking order information if available. */}
            {hasValue(onliineBookingInformation)
              && (
              <OrderSummaryItem
                context={context}
                label={Drupal.t('Delivery Date & Time', {}, { context: 'online_booking' })}
                value={parse(onliineBookingInformation)}
              />
              )}
            <OrderSummaryItem context={context} label={etaLabel} value={expectedDelivery} />
          </ConditionalView>
          <OrderSummaryItem context={context} label={Drupal.t('number of items')} value={itemsCount} />
        </div>
      </div>
      <ConditionalView condition={isAuraEnabled()}>
        <AuraEarnOrderSummaryItem
          pointsEarned={accruedPoints}
          animationDelay="0.8s"
          context={context}
          loyaltyStatus={loyaltyStatus}
        />
        <AuraRedeemOrderSummaryItem
          pointsRedeemed={redeemedPoints}
          animationDelay="1s"
        />
      </ConditionalView>
    </div>
  );
};

export default OrderSummary;
