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
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../../../js/utilities/showRemoveFullScreenLoader';
import { getReturnsByOrderId } from '../../../../../alshaya_online_returns/js/utilities/return_api_helper';

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
      const { orderEntityId } = drupalSettings.onlineReturns;
      getReturnsByOrderId(orderEntityId).then((returnResponse) => {
        if (hasValue(returnResponse) && hasValue(returnResponse.data)
          && hasValue(returnResponse.data.items)) {
          const allReturns = [];
          // Looping through each return items.
          // If return item id matches with order api responses, we
          // merge both the api responses and prepare complete product data.
          returnResponse.data.items.forEach((returnItem) => {
            const itemsData = [];
            returnItem.items.forEach((item) => {
              const { products } = drupalSettings.onlineReturns;
              if (hasValue(products)) {
                const productsObj = Object.values(products);
                const productDetails = productsObj.find((e) => e.item_id === item.order_item_id);
                if (hasValue(productDetails)) {
                  const mergedItem = Object.assign(productDetails, { returnData: item });
                  itemsData.push(mergedItem);
                }
              }
            });
            // Here, returnInfo consists of return api related information
            // and items has all info related to products including return details
            // like how many quantities of item were returned.
            const returnData = {
              returnInfo: returnItem,
              items: itemsData,
            };
            allReturns.push(returnData);
          });
          this.setState({ returns: allReturns });
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
          <OrderReturnEligibility order={order} returns={returns} />
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

          <div className="total-refund-row">
            <div className="desktop-only">&nbsp;</div>
            <div className="above-mobile">&nbsp;</div>
            <div className="right--align">
              <div className="dark upcase">{Drupal.t('Total Refund Amount')}</div>
            </div>
            <div className="blend">
              <div className="dark">
                {/* @todo: Replace with actual value once available in data.
                Markup to be similar to order total. */}
                KWD 1234.123
              </div>
            </div>
            <div className="above-mobile empty--cell">&nbsp;</div>
          </div>
        </div>
      </>
    );
  }
}

export default OrderDetails;
