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
import isOnlineReturnsEnabled from '../../../../../js/utilities/onlineReturnsHelper';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { getReturns } from '../../../../../alshaya_online_returns/js/utilities/order_details_util';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../../../js/utilities/showRemoveFullScreenLoader';

class OrderDetails extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      returns: [],
      loading: false,
      order: drupalSettings.order,
    };
  }

  componentDidMount() {
    showFullScreenLoader();
    if (isOnlineReturnsEnabled()) {
      this.setState({ loading: true });
      getReturns().then((returnResponse) => {
        if (hasValue(returnResponse)) {
          this.setState({ returns: returnResponse });
        }
        this.setState({ loading: false });
      });
    }
  }

  render() {
    const {
      loading,
      returns,
      order,
    } = this.state;

    if (loading) {
      return null;
    }
    removeFullScreenLoader();

    return (
      <>
        <div className={`user__order--detail ${order.auraEnabled ? 'has-aura-points' : ''}`}>
          <OrderSummary order={order} />
          <OrderReturnEligibility order={order} />
          <OnlineBooking order={order} />
          <OrderDeliveryDetails order={order} />
          <OrderReturnInitiated order={order} returns={returns} />
          <OrderItems products={order.products} />
          <OrderCancelledItems order={order} />

          { isOnlineReturnsEnabled() && hasValue(order.online_returns_status) && (
            <ReturnedItemsListing returns={returns} />
          )}

          <div className="sub-total-row">
            <div className="desktop-only">&nbsp;</div>
            <div className="above-mobile">&nbsp;</div>
            <div className="right--align">{Drupal.t('Subtotal')}</div>
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
            </div>
            <div className="warm--white">
              <div className="dark">
                {parse(order.order_details.order_total)}
              </div>
            </div>
            <div className="above-mobile empty--cell">&nbsp;</div>
          </div>

          { hasValue(order.vat_text) && (
            <div className="total-row vat-row collapse-row">
              <div className="warm--white">
                <div className="dark">{order.vat_text}</div>
              </div>
            </div>
          )}
        </div>

        { hasValue(order.refund_text) && (
          <div className="order-item-row">
            <div>
              <div className="tooltip-anchor cancelled-item-tooltip-refund-text info">
                <span>
                  {order.refund_text}
                </span>
              </div>
            </div>
          </div>
        )}
      </>
    );
  }
}

export default OrderDetails;
