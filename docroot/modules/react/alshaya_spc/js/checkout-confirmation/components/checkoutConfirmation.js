import React from 'react';
import Loading from '../../utilities/loading';
import OrderSummary from './OrderSummary';
import { fetchOrderData } from '../../utilities/get_order';
import { stickySidebar } from '../../utilities/stickyElements/stickyElements';
import { redirectToCart } from '../../utilities/get_cart';

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
      // Fetch order data.
      const orderData = fetchOrderData('last');

      if (orderData instanceof Promise) {
        orderData.then((result) => {
          const prevState = this.state;
          this.setState({ ...prevState, wait: false, order: result });
          // Dispatch event for GTM.
          var event = new CustomEvent('orderPaymentMethod', { bubbles: true, detail: { data: result } });
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
        </div>
        <div className="spc-post-content" />
      </>
    );
  }
}

export default CheckoutConfirmation;
