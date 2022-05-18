import React from 'react';
import parse from 'html-react-parser';
import Aura from '../aura';
import TotalItemCount from '../total-item-count';
import CancelledItems from '../cancelled-items';
import OnlineReturns from '../online-returns';
import OnlineBooking from '../online-booking';
import DeliveryDetailNotice from '../delivery-detail-notice';
import DeliveryDetails from '../delivery-details';
import OrderItems from '../order-items';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';

const UserOrderDetails = () => {
  const order = drupalSettings.order_details;

  return (
    <>
      <div className={`user__order--detail ${order.aura_class}`}>
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

        <OnlineReturns order={order} />
        <OnlineBooking order={order} />
        <DeliveryDetailNotice order={order} />

        <div className="order-details-row">
          <DeliveryDetails order={order} />
        </div>

        <ConditionalView condition={hasValue(order.online_returns_status)}>
          <div className="order-item-row" id="online-return-initiated">@todo make react component render here using states</div>
          <div className="order-item-row delivered-items">
            <div>
              <div>{Drupal.t('Delivered Items')}</div>
            </div>
          </div>
        </ConditionalView>

        <OrderItems products={order.products} />
        <ConditionalView condition={hasValue(order.cancelled_items_count)}>
          <div id="cancelled-items" className="order-item-row">
            <div>
              <div>{Drupal.t('Cancelled Items')}</div>
            </div>
          </div>
          <OrderItems products={order.cancelled_products} cancelled />
        </ConditionalView>

        <ConditionalView condition={hasValue(order.online_returns_status)}>
          <div className="order-item-row" id="online-returned-items">@todo make react component render here using states</div>
        </ConditionalView>

        <div className="sub-total-row">
          <div className="desktop-only">&nbsp;</div>
          <div className="above-mobile">&nbsp;</div>
          <div className="right--align">{Drupal.t('Subtotal')}</div>
          <div className="blend">
            {parse(order.order_details.sub_total)}
          </div>
          <div className="above-mobile empty--cell">&nbsp;</div>
        </div>

        <ConditionalView condition={hasValue(order.order_details.discount)}>
          <div className="discount-row collapse-row">
            <div className="desktop-only">&nbsp;</div>
            <div className="above-mobile">&nbsp;</div>
            <div className="right--align">{Drupal.t('Discount')}</div>
            <div className="blend">
              {parse(order.order_details.discount)}
            </div>
            <div className="above-mobile empty--cell">&nbsp;</div>
          </div>
        </ConditionalView>

        <ConditionalView condition={hasValue(order.order_details.delivery_charge)}>
          <div className="delivery-charge-row">
            <div className="desktop-only">&nbsp;</div>
            <div className="above-mobile">&nbsp;</div>
            <div className="right--align">{order.order_details.is_pudo_pickup ? Drupal.t('Collection Charge') : Drupal.t('Delivery charge')}</div>
            <div className="blend">
              {parse(order.order_details.delivery_charge)}
            </div>
            <div className="above-mobile empty--cell">&nbsp;</div>
          </div>
        </ConditionalView>

        <ConditionalView condition={hasValue(order.order_details.surcharge)}>
          <div className="surcharge-row collapse-row">
            <div className="desktop-only">&nbsp;</div>
            <div className="above-mobile">&nbsp;</div>
            <div className="right--align">{order.order_details.surcharge_label}</div>
            <div className="blend">
              {parse(order.order_details.surcharge)}
            </div>
            <div className="above-mobile empty--cell">&nbsp;</div>
          </div>
        </ConditionalView>

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

        <ConditionalView condition={hasValue(order.vat_text)}>
          <div className="total-row vat-row collapse-row">
            <div className="warm--white">
              <div className="dark">{order.vat_text}</div>
            </div>
          </div>
        </ConditionalView>
      </div>
    </>
  );
};

export default UserOrderDetails;
