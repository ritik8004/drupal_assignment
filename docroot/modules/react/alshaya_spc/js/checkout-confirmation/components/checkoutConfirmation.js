import React from 'react';
import Cookies from 'js-cookie';
import OrderSummaryBlock from '../../utilities/order-summary-block';
import Loading from '../../utilities/loading';
import OrderSummary from './OrderSummary';
import { fetchOrderData } from '../../utilities/get_order';
import { stickySidebar } from '../../utilities/stickyElements/stickyElements';
import { redirectToCart } from '../../utilities/get_cart';
import { removeCartFromStorage } from '../../utilities/storage';

class CheckoutConfirmation extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: true,
      order: null,
    };
  }

  componentDidMount() {
    try {
      if (Cookies.get('middleware_order_placed')) {
        removeCartFromStorage();
        Cookies.remove('middleware_order_placed');
      }

      // Fetch order data.
      const orderData = fetchOrderData('last');

      if (orderData instanceof Promise) {
        orderData.then((result) => {
          const prevState = this.state;
          this.setState({ ...prevState, wait: false, order: result });
          // Dispatch event for GTM.
          const event = new CustomEvent('orderPaymentMethod', { bubbles: true, detail: { data: result } });
          document.dispatchEvent(event);
        });
      }
    } catch (e) {
      redirectToCart();
    }

    // Make sidebar sticky.
    stickySidebar();
  }

  render() {
    const { wait } = this.state;
    // While page loads and all info available.
    if (wait) {
      return <Loading loadingMessage={Drupal.t('loading order ...')} />;
    }
    const items_qty = drupalSettings.order_details.number_of_items;
    const items = drupalSettings.order_details.items;
    const totals = drupalSettings.order_details.totals;
    const in_stock = [];
    const promo = [];

    return (
      <>
        <div className="spc-pre-content">
          <div className="impress-msg">{Drupal.t('Thanks for shopping with us.')}</div>
          <div className="impress-subtitle">{Drupal.t('Here\'s a confirmation of your order and all the details you may need.')}</div>
        </div>
        <div className="spc-main">
          <div className="spc-content">
            <OrderSummary />
          </div>
          <div className="spc-sidebar">
            <OrderSummaryBlock item_qty={items_qty} items={items} totals={totals} in_stock={in_stock} cart_promo={promo} show_checkout_button={false} />
          </div>
        </div>
        <div className="spc-post-content" />
      </>
    );
  }
}

export default CheckoutConfirmation;
