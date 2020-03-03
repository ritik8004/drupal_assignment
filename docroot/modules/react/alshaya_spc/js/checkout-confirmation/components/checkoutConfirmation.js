import React from 'react';
import OrderSummaryBlock from '../../utilities/order-summary-block';
import { fetchCartData } from '../../utilities/get_cart';
import { addInfoInStorage, getInfoFromStorage } from '../../utilities/storage';
import { checkCartCustomer } from '../../utilities/cart_customer_util';
import { stickySidebar } from '../../utilities/stickyElements/stickyElements';
import Loading from '../../utilities/loading';
import EmptyResult from '../../utilities/empty-result';
import OrderSummary from './OrderSummary';

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
      // Fetch cart data.
      const cart_data = fetchCartData();
      if (cart_data instanceof Promise) {
        cart_data.then((result) => {
          let cart_data = getInfoFromStorage();
          if (!cart_data) {
            cart_data = { cart: result };
          }

          addInfoInStorage(cart_data);
          checkCartCustomer(cart_data);
          this.setState({
            wait: false,
            cart: cart_data,
          });
        });
      }
    } catch (error) {
      // In case of error, do nothing.
    }

    // Make sidebar sticky.
    stickySidebar();
  }

  render() {
    // While page loads and all info available.
    if (this.state.wait) {
      return <Loading loadingMessage={Drupal.t('loading checkout ...')} />;
    }

    // If cart not available.
    if (this.state.cart === null) {
      return (
        <>
          <EmptyResult Message={Drupal.t('your shopping basket is empty.')} />
        </>
      );
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
          <div className="spc-sidebar">
            <OrderSummaryBlock item_qty={this.state.cart.cart.items_qty} items={this.state.cart.cart.items} totals={this.state.cart.cart.totals} in_stock={this.state.cart.cart.in_stock} cart_promo={this.state.cart.cart.cart_promo} show_checkout_button={false} />
          </div>
        </div>
        <div className="spc-post-content" />
      </>
    );
  }
}

export default CheckoutConfirmation;
