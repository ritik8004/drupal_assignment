import React from 'react';
import Loading from '../../utilities/loading';
import OrderSummary from './OrderSummary';
import { fetchOrderData } from '../../utilities/get_order';

class CheckoutConfirmation extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: true,
      cart: null,
    };
  }

  componentDidMount() {
    try {
      // Fetch order data.
      const order_data = fetchOrderData('last');

      if (order_data instanceof Promise) {
        order_data.then((result) => {
          const prevState = this.state;
          this.setState({ ...prevState, wait: false });

          console.log(result);
        });
      }
    } catch (error) {
      console.error(error);
    }
  }

  render() {
    // While page loads and all info available.
    if (this.state.wait) {
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
