import React from 'react';
import parse from 'html-react-parser';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

const DeliveryDetails = (props) => {
  const { order } = props;
  const orderDetails = order.order_details;

  return (
    <>
      {/* @todo test this */}
      <div className="order__user--info">
        { orderDetails.type === 'cc' && (
          <>
            <div className="label font-small">{Drupal.t('Collection Address')}</div>
            <div className="store-name-and-address">
              <div className="store-name dark">{orderDetails.store_name}</div>
              <div className="store-address">{orderDetails.store_address}</div>
              <div className="store-phone no-direction">{orderDetails.store_phone}</div>
              <div className="store-map-link">
                <a href={orderDetails.view_on_map_link} target="_blank" rel="noopener noreferrer">{Drupal.t('View on map')}</a>
              </div>
            </div>

            <div className="contact">
              <div className="label font-small">{Drupal.t('Collector contact no.')}</div>
              <div className="dark no-direction">{orderDetails.contact_no}</div>
            </div>
          </>
        )}

        { orderDetails.type !== 'cc' && hasValue(orderDetails.delivery_address) && (
          <>
            <div className="label font-small">{Drupal.t('Delivery details')}</div>
            <div>
              {parse(orderDetails.delivery_address)}
            </div>
          </>
        )}

        { hasValue(orderDetails.payment_method) && (
          <div className="mobile-only payment--details">
            <div className="label font-small">{Drupal.t('Payment method')}</div>
            <div className="dark">{orderDetails.payment_method}</div>
          </div>
        )}

        { hasValue(orderDetails.payment.referenceNumber) && (
          <div className="mobile-only payment--details">
            <div className="label font-small">{Drupal.t('Reference No')}</div>
            <div className="dark">{orderDetails.payment.referenceNumber}</div>
          </div>
        )}

        { hasValue(orderDetails.payment.banktransfer_payment_details) && (
          <div className="mobile-only banktransfer">{orderDetails.payment.banktransfer_payment_details}</div>
        )}

        { hasValue(orderDetails.payment.transactionId) && (
          <div className="mobile-only transaction-id">
            <div className="label font-small">{Drupal.t('Transaction ID')}</div>
            <div className="dark">{orderDetails.payment.transactionId}</div>
          </div>
        )}

        { hasValue(orderDetails.payment.paymentId) && (
          <div className="mobile-only payment-id">
            <div className="label font-small">{Drupal.t('Payment ID')}</div>
            <div className="dark">{orderDetails.payment.paymentId}</div>
          </div>
        )}

        { hasValue(orderDetails.payment.resultCode) && (
          <div className="mobile-only result-code">
            <div className="label font-small">{Drupal.t('Result code')}</div>
            <div className="dark">{orderDetails.payment.resultCode}</div>
          </div>
        )}
      </div>
      <div className="order__delivery-details">
        {/* @todo test this */}
        { orderDetails.type === 'cc' && hasValue(orderDetails.store_open_hours) && (
          <>
            <div className="label font-small">{Drupal.t('Collection times')}</div>
            <div>
              <div className="store-open-hours">
                <div className="hours--wrapper selector--hours">
                  <div className="open--hours">
                    {Object.keys(orderDetails.store_open_hours).map((item) => (
                      <div>
                        <span className="key-value-key dark">{item.key}</span>
                        {/* @todo test this */}
                        <span className="key-value-value">{hasValue(item.value) ? item.value : Drupal.t('Holiday')}</span>
                      </div>
                    ))}
                  </div>
                </div>
              </div>
            </div>
          </>
        )}

        { orderDetails.type !== 'cc' && hasValue(orderDetails.billing_address) && (
          <>
            <div className="label font-small">{Drupal.t('Billing details')}</div>
            <div>
              {parse(orderDetails.billing_address)}
            </div>
          </>
        )}

        {/* @todo test this */}
        { hasValue(orderDetails.delivery_method) && (
          <div className="mobile-only delivery--method">
            <div className="label font-small">{Drupal.t('Delivery method')}</div>
            <div className="dark">{orderDetails.delivery_method}</div>
          </div>
        )}
      </div>

      <div className="desktop-only">
        { orderDetails.type === 'cc' && hasValue(orderDetails.billing_address) && (
          <>
            <div className="label font-small">{Drupal.t('Billing details')}</div>
            <div>
              {parse(orderDetails.billing_address)}
            </div>
          </>
        )}
      </div>
      <div className="above-mobile blend">
        <div className="label payment-method font-small">{Drupal.t('Payment method')}</div>
        <div className="dark">{orderDetails.payment_method}</div>

        { hasValue(orderDetails.payment.referenceNumber) && (
          <>
            <div className="label delivery-method font-small">{Drupal.t('Reference No')}</div>
            <div className="dark reference-no">{orderDetails.payment.referenceNumber}</div>
          </>
        )}

        { hasValue(orderDetails.payment.banktransfer_payment_details) && (
          <>
            {orderDetails.payment.banktransfer_payment_details}
          </>
        )}

        { hasValue(orderDetails.payment.transactionId) && (
          <>
            <div className="label transaction-id font-small">{Drupal.t('Transaction ID')}</div>
            <div className="dark">{orderDetails.payment.transactionId}</div>
          </>
        )}

        { hasValue(orderDetails.payment.paymentId) && (
          <>
            <div className="label payment-id font-small">{Drupal.t('Payment ID')}</div>
            <div className="dark">{orderDetails.payment.paymentId}</div>
          </>
        )}

        { hasValue(orderDetails.payment.resultCode) && (
          <>
            <div className="label result-code font-small">{Drupal.t('Result code')}</div>
            <div className="dark">{orderDetails.payment.resultCode}</div>
          </>
        )}

        { hasValue(orderDetails.delivery_method) && (
          <>
            <div className="label delivery-method font-small">{Drupal.t('Delivery method')}</div>
            <div className="dark">{orderDetails.delivery_method}</div>
          </>
        )}
      </div>
      <div className="above-mobile empty--cell">&nbsp;</div>
    </>
  );
};

export default DeliveryDetails;
