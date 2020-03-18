import React from 'react';
import Cookies from 'js-cookie';
import OrderSummaryBlock from '../../utilities/order-summary-block';
import OrderSummary from './OrderSummary';
import { stickySidebar } from '../../utilities/stickyElements/stickyElements';
import { removeCartFromStorage } from '../../utilities/storage';

class CheckoutConfirmation extends React.Component {
  constructor(props) {
    super(props);

    try {
      if (Cookies.get('middleware_order_placed')) {
        removeCartFromStorage();
        Cookies.remove('middleware_order_placed');
      }
    } catch (e) {
      window.location = Drupal.url('cart');
    }
  }

  componentDidMount() {
    // Make sidebar sticky.
    stickySidebar();
  }

  render() {
    const { items, totals, number_of_items: itemsTotal } = drupalSettings.order_details;

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
            <OrderSummaryBlock
              item_qty={itemsTotal}
              items={items}
              totals={totals}
              cart_promo={[]}
              show_checkout_button={false}
            />
          </div>
        </div>
        <div className="spc-post-content" />
      </>
    );
  }
}

export default CheckoutConfirmation;
