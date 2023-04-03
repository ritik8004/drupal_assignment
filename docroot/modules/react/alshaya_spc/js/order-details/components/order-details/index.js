import React from 'react';
import parse from 'html-react-parser';
import OrderSummary from '../order-summary';
import OnlineBooking from '../online-booking';
import OrderDeliveryDetails from '../order-delivery-details';
import OrderItems from '../order-items';
import OrderCancelledItems from '../order-cancelled-items';
import OrderReturnEligibility from '../order-return-eligibility';
import OrderReturnInitiated from '../order-return-initiated';
import ReturnedItemsListing from '../../../../../alshaya_online_returns/js/order-details/returned-items-listing';
import {
  getProcessedReturnsData,
  isOnlineReturnsEnabled,
} from '../../../../../js/utilities/onlineReturnsHelper';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../../../js/utilities/showRemoveFullScreenLoader';
import PriceElement from '../../../../../js/utilities/components/price/price-element';
import ErrorMessage from '../../../../../js/utilities/components/error-message';
import smoothScrollTo from '../../../../../js/utilities/components/smooth-scroll';

class OrderDetails extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      returns: [],
      loading: false,
      order: null,
      totalRefundAmount: 0,
      errorMessage: '',
    };
  }

  componentDidMount() {
    this.setState({ loading: true });
    showFullScreenLoader('order-details');
    window.commerceBackend.getOrderDetailsData().then((orderDetails) => {
      this.setState({ order: orderDetails });
      // If Online Returns is enabled we need to process order data
      // based on returns.
      if (isOnlineReturnsEnabled()) {
        showFullScreenLoader('order-details');
        this.processOrderData();
      } else {
        this.setState({ loading: false });
      }
    });
  }

  processOrderData = async () => {
    const { orderEntityId, refunded_products: products } = drupalSettings.onlineReturns;

    const returnsData = await getProcessedReturnsData(orderEntityId, products);

    this.setState({
      loading: false,
      ...returnsData,
    });
  }

  handleErrorMessage = (errorMessage) => {
    if (hasValue(errorMessage)) {
      this.setState({ errorMessage });
      // Scroll user to the top parent wrapper.
      if (document.getElementsByClassName('user__order--detail').length > 0) {
        smoothScrollTo('.user__order--detail');
      }
    }
  };

  render() {
    const {
      loading,
      returns,
      order,
      totalRefundAmount,
      errorMessage,
    } = this.state;

    if (loading || !hasValue(order)) {
      return null;
    }

    removeFullScreenLoader('order-details');

    return (
      <>
        <div className={`user__order--detail ${order.auraEnabled ? 'has-aura-points' : ''}`}>
          { hasValue(errorMessage) && (
            <ErrorMessage message={errorMessage} />
          )}
          <OrderSummary order={order} />
          <OrderReturnEligibility order={order} returns={returns} />
          <OnlineBooking order={order} />
          <OrderDeliveryDetails order={order} />
          <OrderReturnInitiated
            order={order}
            returns={returns}
            handleErrorMessage={this.handleErrorMessage}
          />
          <OrderItems products={order.products} />
          <OrderCancelledItems order={order} />

          { isOnlineReturnsEnabled() && hasValue(order.online_returns_status) && (
            <ReturnedItemsListing returns={returns} />
          )}

          <div className="sub-total-row">
            <div className="desktop-only">&nbsp;</div>
            <div className="above-mobile">&nbsp;</div>
            <div className="right--align">{Drupal.t('Sub total')}</div>
            <div className="blend">
              {parse(order.order_details.sub_total)}
            </div>
            <div className="above-mobile empty--cell">&nbsp;</div>
          </div>

          { hasValue(order.order_details.discount) && (
            <div className="discount-row collapse-row">
              <div className="desktop-only">&nbsp;</div>
              <div className="above-mobile">&nbsp;</div>
              <div className="right--align">{Drupal.t('Discount')}</div>
              <div className="blend">
                {parse(order.order_details.discount)}
              </div>
              <div className="above-mobile empty--cell">&nbsp;</div>
            </div>
          )}

          {hasValue(order.order_details.voucher_discount) && (
            <div className="discount-row collapse-row hm-voucher-discount">
              <div className="desktop-only">&nbsp;</div>
              <div className="above-mobile">&nbsp;</div>
              <div className="right--align">
                {order.order_details.voucher_discount_label}
              </div>
              <div className="blend">
                {parse(order.order_details.voucher_discount)}
              </div>
              <div className="above-mobile empty--cell">&nbsp;</div>
            </div>
          )}

          { hasValue(order.order_details.delivery_charge) && (
            <div className="delivery-charge-row">
              <div className="desktop-only">&nbsp;</div>
              <div className="above-mobile">&nbsp;</div>
              <div className="right--align">{order.order_details.is_pudo_pickup ? Drupal.t('Collection Charge') : Drupal.t('Delivery charge')}</div>
              <div className="blend">
                {parse(order.order_details.delivery_charge)}
              </div>
              <div className="above-mobile empty--cell">&nbsp;</div>
            </div>
          )}

          { hasValue(order.order_details.duties_taxes) && (
            <div className="duties-taxes-row">
              <div className="desktop-only">&nbsp;</div>
              <div className="above-mobile">&nbsp;</div>
              <div className="right--align">{ Drupal.t('Duties and Taxes') }</div>
              <div className="blend">
                {parse(order.order_details.duties_taxes)}
              </div>
              <div className="above-mobile empty--cell">&nbsp;</div>
            </div>
          )}

          { hasValue(order.order_details.surcharge)
          && hasValue(order.order_details.surcharge_label) && (
            <div className="surcharge-row collapse-row">
              <div className="desktop-only">&nbsp;</div>
              <div className="above-mobile">&nbsp;</div>
              <div className="right--align">
                {order.order_details.surcharge_label}
              </div>
              <div className="blend">
                {parse(order.order_details.surcharge)}
              </div>
              <div className="above-mobile empty--cell">&nbsp;</div>
            </div>
          )}

          <div className="total-row">
            <div className="desktop-only">&nbsp;</div>
            <div className="above-mobile">&nbsp;</div>
            <div className="right--align">
              <div className="dark upcase">{Drupal.t('Order Total')}</div>
              { hasValue(order.vat_text) && (
                <div className="total-row vat-row collapse-row">
                  <div className="warm--white">
                    <div className="dark">
                      {'('}
                      {order.vat_text}
                      {')'}
                    </div>
                  </div>
                </div>
              )}
            </div>
            <div className="warm--white">
              <div className="dark">
                {parse(order.order_details.order_total)}
              </div>
            </div>
            <div className="above-mobile empty--cell">&nbsp;</div>
          </div>

          {totalRefundAmount > 0 && (
            <div className="total-refund-row">
              <div className="desktop-only">&nbsp;</div>
              <div className="above-mobile">&nbsp;</div>
              <div className="right--align">
                <div className="dark upcase">{Drupal.t('Total Refund Amount', {}, { context: 'online_returns' })}</div>
              </div>
              <div className="blend">
                <div className="dark"><PriceElement amount={totalRefundAmount} /></div>
              </div>
              <div className="above-mobile empty--cell">&nbsp;</div>
            </div>
          )}
        </div>
      </>
    );
  }
}

export default OrderDetails;
