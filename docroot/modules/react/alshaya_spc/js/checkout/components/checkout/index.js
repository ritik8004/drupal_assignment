import React from 'react';
import ClicknCollectContextProvider from '../../../context/ClicknCollect';
import { checkCartCustomer } from '../../../utilities/cart_customer_util';
import EmptyResult from '../../../utilities/empty-result';
import { fetchCartData } from '../../../utilities/get_cart';
import Loading from '../../../utilities/loading';
import OrderSummaryBlock from '../../../utilities/order-summary-block';
import { stickySidebar } from '../../../utilities/stickyElements/stickyElements';
import {
  addInfoInStorage,
  getInfoFromStorage,
  removeCartFromStorage
} from '../../../utilities/storage';
import CompletePurchase from '../complete-purchase';
import DeliveryInformation from '../delivery-information';
import DeliveryMethods from '../delivery-methods';
import PaymentMethods from '../payment-methods';
import TermsConditions from '../terms-conditions';
import {removeFullScreenLoader} from "../../../utilities/checkout_util";

export default class Checkout extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      wait: true,
      cart: null,
      payment_methods: window.drupalSettings.payment_methods,
    };
  }

  componentDidMount() {
    try {
      // If logged in user.
      if (window.drupalSettings.user.uid > 0) {
        let temp_cart = getInfoFromStorage();
        // If cart available in storage and shipping address
        // already not set in cart and user has address
        // available, remove cart from local storage so
        // so that fresh cart is fetched and thus shipping
        // info can be set in cart.
        if (temp_cart !== null
          && (temp_cart.cart === undefined
            || (temp_cart.cart.shipping_address === null
          && window.drupalSettings.user_name.address_available))) {
          removeCartFromStorage();
        }
      }

      // Fetch cart data.
      var cart_data = fetchCartData();
      if (cart_data instanceof Promise) {
        cart_data.then((result) => {
          let cart_obj = getInfoFromStorage();
          if (!cart_obj) {
            cart_obj = { cart: result };
          }
          addInfoInStorage(cart_obj);
          checkCartCustomer(cart_obj).then(updated => {
            if (updated) {
              cart_obj = getInfoFromStorage();
            }
            window.cart_data = cart_obj;
            this.setState({
              wait: false,
              cart: cart_obj
            });
          });

        });
      }
    }
    catch (error) {
      // In case of error, do nothing.
      console.error(error);
    }

    // Make sidebar sticky.
    stickySidebar();
  }

  /**
   * Update the cart in storage.
   */
  refreshCart = (cart) => {
    this.setState({
      cart: cart
    });

    addInfoInStorage(cart);
    removeFullScreenLoader();
  };

  render() {
    // While page loads and all info available.

    if (this.state.wait) {
      return <Loading />
    }

    // If cart not available.
    if (this.state.cart === null) {
      return (
        <React.Fragment>
          <EmptyResult Message={Drupal.t('your shopping basket is empty.')} />
        </React.Fragment>
      );
    }

    return (
      <React.Fragment>
        <div className="spc-pre-content" />
        <div className="spc-main">
          <div className="spc-content">
            <DeliveryMethods cart={this.state.cart} refreshCart={this.refreshCart} />
            <ClicknCollectContextProvider cart={this.state.cart}>
              <DeliveryInformation refreshCart={this.refreshCart} cart={this.state.cart} />
            </ClicknCollectContextProvider>
            <PaymentMethods refreshCart={this.refreshCart} payment_methods={this.state.payment_methods} cart={this.state.cart} />
            {window.innerWidth > 768 &&
              <TermsConditions />
            }
            <CompletePurchase cart={this.state.cart} />
          </div>
          <div className="spc-sidebar">
            <OrderSummaryBlock item_qty={this.state.cart.cart.items_qty} items={this.state.cart.cart.items} totals={this.state.cart.cart.totals} in_stock={this.state.cart.cart.in_stock} cart_promo={this.state.cart.cart.cart_promo} show_checkout_button={false} />
          </div>
        </div>
        <div className="spc-post-content">
          {window.innerWidth < 768 &&
            <TermsConditions />
          }
        </div>
      </React.Fragment>
    );
  }

}
