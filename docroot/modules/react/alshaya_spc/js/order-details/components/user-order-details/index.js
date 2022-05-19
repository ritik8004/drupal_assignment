import React from 'react';
import parse from 'html-react-parser';
import Aura from '../aura';
import TotalItemCount from '../total-item-count';
import CancelledItems from '../cancelled-items';
import OnlineReturnEligibility from '../online-return-eligibility';
import OnlineBooking from '../online-booking';
import DeliveryDetailNotice from '../delivery-detail-notice';
import DeliveryDetails from '../delivery-details';
import OrderItems from '../order-items';
import isOnlineReturnsEnabled from '../../../../../js/utilities/onlineReturnsHelper';
import ReturnInitiated from '../../../../../alshaya_online_returns/js/order-details/return-initiated';
import ReturnedItemsListing from '../../../../../alshaya_online_returns/js/order-details/returned-items-listing';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { getReturns } from '../../../../../alshaya_online_returns/js/utilities/order_details_util';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../../../js/utilities/showRemoveFullScreenLoader';

class UserOrderDetails extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      returns: [],
      loading: true,
      order: drupalSettings.order,
    };
  }

  componentDidMount() {
    if (isOnlineReturnsEnabled()) {
      getReturns().then((returnResponse) => {
        if (hasValue(returnResponse)) {
          this.setState({ returns: returnResponse });
          this.setState({ loading: false });
        }
      });
    } else {
      this.setState({ loading: false });
    }
  }

  render() {
    const {
      loading,
      returns,
      order,
    } = this.state;

    if (loading) {
      showFullScreenLoader();
      return null;
    }
    removeFullScreenLoader();

    return (
      <>
        <div className={`user__order--detail ${order.auraEnabled ? 'has-aura-points' : ''}`}>
          <div className="order-summary-row">
            <div className="order-transaction">
              <div className="light font-small">{ Drupal.t('Order ID') }</div>
              <div className="dark">{ order.orderId }</div>
              <div className="light font-small">{ order.orderDate }</div>

              <div className="mobile-only">
                <div className="dark">
                  { order.name }
                  ...
                </div>
                <div className="light">
                  <TotalItemCount order={order} />
                  <CancelledItems order={order} />
                </div>
              </div>
            </div>

            <div className="above-mobile order-name">
              <div className="dark">
                <div className="dark">
                  { order.name }
                  ...
                </div>
              </div>
              <div className="light">
                <TotalItemCount order={order} />
                <CancelledItems order={order} />
              </div>

              <div className="tablet-only">
                <div className={`button ${order.status.class}`}>{order.status.text}</div>
              </div>
            </div>

            <div className="mobile-only">
              <div className={`button ${order.status.class}`}>{order.status.text}</div>
            </div>

            <div className="desktop-only order__summary--status">
              <div className={`button ${order.status.class}`}>{order.status.text}</div>
            </div>

            <div className="above-mobile blend order-total">
              <div className="light">{Drupal.t('Order Total')}</div>
              <div className="dark">
                {parse(order.order_details.order_total)}
              </div>
            </div>

            <Aura order={order} />
          </div>

          <OnlineReturnEligibility order={order} />
          <OnlineBooking order={order} />
          <DeliveryDetailNotice order={order} />

          <div className="order-details-row">
            <DeliveryDetails order={order} />
          </div>

          { isOnlineReturnsEnabled() && hasValue(order.online_returns_status) && (
            <>
              <ReturnInitiated returns={returns} />
              <div className="order-item-row delivered-items">
                <div>
                  <div>{Drupal.t('Delivered Items')}</div>
                </div>
              </div>
            </>
          )}

          <OrderItems products={order.products} />
          { hasValue(order.cancelled_items_count) && (
            <>
              <div id="cancelled-items" className="order-item-row cancelled-items">
                <div>
                  <div>{Drupal.t('Cancelled Items')}</div>
                </div>
              </div>
              <OrderItems products={order.cancelled_products} cancelled />
            </>
          )}

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

          { hasValue(order.order_details.surcharge) && (
            <div className="surcharge-row collapse-row">
              <div className="desktop-only">&nbsp;</div>
              <div className="above-mobile">&nbsp;</div>
              <div className="right--align">{order.order_details.surcharge_label}</div>
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

export default UserOrderDetails;
