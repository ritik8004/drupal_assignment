import React from 'react';
import ClicknCollectContextProvider from '../../../context/ClicknCollect';
import { checkCartCustomer } from '../../../utilities/cart_customer_util';
import EmptyResult from '../../../utilities/empty-result';
import { fetchCartData } from '../../../utilities/get_cart';
import Loading from '../../../utilities/loading';
import OrderSummaryBlock from '../../../utilities/order-summary-block';
import HDBillingAddress from '../hd-billing-address';
import CnCBillingAddress from '../cnc-billing-address';
import { stickySidebar } from '../../../utilities/stickyElements/stickyElements';
import {
  addInfoInStorage,
  getInfoFromStorage,
  removeCartFromStorage,
} from '../../../utilities/storage';
import CompletePurchase from '../complete-purchase';
import DeliveryInformation from '../delivery-information';
import DeliveryMethods from '../delivery-methods';
import PaymentMethods from '../payment-methods';
import CheckoutMessage from '../../../utilities/checkout-message';
import TermsConditions from '../terms-conditions';
import { getLocationAccess, getDefaultMapCenter } from '../../../utilities/checkout_util';
import { fetchClicknCollectStores } from "../../../utilities/api/requests";
import { createFetcher } from '../../../utilities/api/fetcher';
import {removeFullScreenLoader} from "../../../utilities/checkout_util";
import _isEmpty from 'lodash/isEmpty';
import ConditionalView from '../../../common/components/conditional-view';
import Cookies from 'js-cookie';

window.fetchStore = 'idle';

export default class Checkout extends React.Component {
  constructor(props) {
    super(props);

    this.paymentMethods = React.createRef();

    this.state = {
      wait: true,
      cart: null,
      storeList: null,
      message_type: null,
      error_success_message: null,
    };
  }

  componentDidMount() {
    try {
      if (Cookies.get('middleware_payment_error')) {
        // @TODO: Show error message in error component.
        alert(Cookies.get('middleware_payment_error'));
        Cookies.remove('middleware_payment_error');
      }
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
    // If there is error on cart update.
    if (cart.error_message !== undefined) {
      this.setState({
        message_type: 'error',
        error_success_message: cart.error_message
      });
    }
    else {
      addInfoInStorage(cart);

      this.setState({
        cart: cart,
        message_type: 'success',
        error_success_message: null
      });
    }

    // Remove loader.
    removeFullScreenLoader();
  };

  /**
   * Trigger cnc event to get location details and fetch stores.
   */
  cncEvent = () => {
    let { cart: { store_info } } = this.state.cart;
    if (store_info) {
      this.fetchStoresHelper({
        lat: parseFloat(store_info.lat),
        lng: parseFloat(store_info.lng),
      });
    }
    else {
      getLocationAccess()
        .then(pos => {
          this.fetchStoresHelper({
            lat: pos.coords.latitude,
            lng: pos.coords.longitude,
          });
        },
        reject => {
          this.fetchStoresHelper(getDefaultMapCenter());
        })
        .catch(error => {
          console.log(error);
        });
    }

  };

  /**
   * Fetch click n collect stores and update store list.
   */
  fetchStoresHelper = (coords) => {
    if (_isEmpty(coords)) {
      window.fetchStore = 'finished';
      return;
    }

    window.fetchStore = 'pending';
    const list = createFetcher(fetchClicknCollectStores).read(coords);

    list.then(
      response => {
        if (typeof response.error === 'undefined') {
          this.setState({storeList: response});
          window.fetchStore = 'finished';
        }
      }
    );
  };

  validateBeforePlaceOrder = () => {
    this.paymentMethods.current.validateBeforePlaceOrder();
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

    const termConditions = <TermsConditions />

    return (
      <React.Fragment>
        <div className="spc-pre-content" />
        <div className="spc-main">
          <div className="spc-content">
            {this.state.error_success_message !== null &&
              <CheckoutMessage type={this.state.message_type}>
                {this.state.error_success_message}
              </CheckoutMessage>
            }
            <DeliveryMethods cart={this.state.cart} refreshCart={this.refreshCart} cncEvent={this.cncEvent}/>
            <ClicknCollectContextProvider cart={this.state.cart} storeList={this.state.storeList}>
              <DeliveryInformation refreshCart={this.refreshCart} cart={this.state.cart} />
            </ClicknCollectContextProvider>
            <PaymentMethods ref={this.paymentMethods} refreshCart={this.refreshCart} cart={this.state.cart} />
            {(this.state.cart.cart.delivery_type === 'hd') ? (
              <HDBillingAddress
                refreshCart={this.refreshCart}
                billingAddress={this.state.cart.cart.billing_address}
                shippingAddress={this.state.cart.cart.shipping_address}
                carrierInfo={this.state.cart.cart.carrier_info}
                paymentMethod={this.state.cart.selected_payment_method}
              />
            ) : (
              <CnCBillingAddress
                refreshCart={this.refreshCart}
                billingAddress={this.state.cart.cart.billing_address}
                shippingAddress={this.state.cart.cart.shipping_address}
                carrierInfo={this.state.cart.cart.carrier_info}
                paymentMethod={this.state.cart.selected_payment_method}
              />
            )}
            <ConditionalView condition={window.innerWidth > 768}>
              {termConditions}
            </ConditionalView>
            <CompletePurchase cart={this.state.cart} validateBeforePlaceOrder={this.validateBeforePlaceOrder} />
          </div>
          <div className="spc-sidebar">
            <OrderSummaryBlock item_qty={this.state.cart.cart.items_qty} items={this.state.cart.cart.items} totals={this.state.cart.cart.totals} in_stock={this.state.cart.cart.in_stock} cart_promo={this.state.cart.cart.cart_promo} show_checkout_button={false} />
          </div>
        </div>
        <div className="spc-post-content">
          <ConditionalView condition={window.innerWidth < 768}>
            {termConditions}
          </ConditionalView>
        </div>
      </React.Fragment>
    );
  }

}
