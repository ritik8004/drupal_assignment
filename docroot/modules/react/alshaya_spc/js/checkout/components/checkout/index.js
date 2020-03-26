import React from 'react';
import ClicknCollectContextProvider from '../../../context/ClicknCollect';
import { checkCartCustomer } from '../../../utilities/cart_customer_util';
import EmptyResult from '../../../utilities/empty-result';
import { fetchCartData } from '../../../utilities/api/requests';
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
import { removeFullScreenLoader } from '../../../utilities/checkout_util';
import ConditionalView from '../../../common/components/conditional-view';
import smoothScrollTo from '../../../utilities/smoothScroll';
import VatFooterText from '../../../utilities/vat-footer';

window.fetchStore = 'idle';

export default class Checkout extends React.Component {
  constructor(props) {
    super(props);

    this.paymentMethods = React.createRef();

    this.state = {
      wait: true,
      cart: null,
      messageType: null,
      errorSuccessMessage: null,
    };
  }

  componentDidMount() {
    try {
      // If logged in user.
      if (window.drupalSettings.user.uid > 0) {
        const tempCart = getInfoFromStorage();
        // If cart available in storage and shipping address
        // already not set in cart and user has address
        // available, remove cart from local storage so
        // so that fresh cart is fetched and thus shipping
        // info can be set in cart.
        if (tempCart !== null
          && (tempCart.cart === undefined
            || (tempCart.cart.shipping_address === null
          && window.drupalSettings.user_name.address_available))) {
          removeCartFromStorage();
        }
      }

      // Fetch cart data.
      const cartData = fetchCartData();
      if (cartData instanceof Promise) {
        cartData.then((result) => {
          let cartObj = { cart: result };
          addInfoInStorage(cartObj);
          checkCartCustomer(cartObj).then((updated) => {
            if (updated) {
              cartObj = getInfoFromStorage();
            }
            this.setState({
              wait: false,
              cart: cartObj,
            });
          });
        });
      }
    } catch (error) {
      // In case of error, do nothing.
      Drupal.logJavascriptError('checkout', error);
    }

    // Make sidebar sticky.
    stickySidebar();

    document.addEventListener('spcCheckoutMessageUpdate', this.handleMessageUpdateEvent, false);
  }

  componentWillUnmount() {
    document.removeEventListener('spcCheckoutMessageUpdate', this.handleMessageUpdateEvent, false);
  }

  handleMessageUpdateEvent = (event) => {
    const { type, message } = event.detail;
    this.updateCheckoutMessage(type, message);
  };

  updateCheckoutMessage = (type, message) => {
    this.setState({ messageType: type, errorSuccessMessage: message });
    // Checking length as if no type, means no error.
    if (type.length > 0) {
      smoothScrollTo('.spc-content');
    }
  };

  /**
   * Update the cart in storage.
   */
  refreshCart = (cart) => {
    // Remove loader.
    removeFullScreenLoader();

    // If there is error on cart update.
    if (cart.error_message !== undefined) {
      this.updateCheckoutMessage('error', cart.error_message);
      return;
    }

    // Reset error message.
    this.updateCheckoutMessage('', '');

    addInfoInStorage(cart);
    this.setState({ cart });
  };

  validateBeforePlaceOrder = () => this.paymentMethods.current.validateBeforePlaceOrder();

  /**
   * Get the billing address component for rendering.
   */
  getBillingComponent = () => {
    const { cart } = this.state;

    if (cart.cart.delivery_type === 'hd'
      || (cart.delivery_type !== undefined && cart.delivery_type === 'hd')) {
      return (
        <HDBillingAddress
          refreshCart={this.refreshCart}
          cart={cart}
        />
      );
    }

    return (
      <CnCBillingAddress
        refreshCart={this.refreshCart}
        cart={cart}
      />
    );
  }

  render() {
    const {
      wait,
      cart,
      errorSuccessMessage,
      messageType,
    } = this.state;
    // While page loads and all info available.

    if (wait) {
      return <Loading />;
    }

    // If cart not available.
    if (cart === null) {
      return (
        <>
          <EmptyResult Message={Drupal.t('your shopping basket is empty.')} />
        </>
      );
    }

    const termConditions = <TermsConditions />;
    const billingComponent = this.getBillingComponent();

    return (
      <>
        <div className="spc-pre-content" />
        <div className="spc-main">
          <div className="spc-content">
            {errorSuccessMessage !== null
              && (
              <CheckoutMessage type={messageType} context="page-level-checkout">
                {errorSuccessMessage}
              </CheckoutMessage>
              )}

            <DeliveryMethods cart={cart} refreshCart={this.refreshCart} />
            <ClicknCollectContextProvider cart={cart}>
              <DeliveryInformation refreshCart={this.refreshCart} cart={cart} />
            </ClicknCollectContextProvider>

            <PaymentMethods ref={this.paymentMethods} refreshCart={this.refreshCart} cart={cart} />

            {billingComponent}

            <ConditionalView condition={window.innerWidth > 768}>
              <VatFooterText />
              {termConditions}
            </ConditionalView>

            <CompletePurchase
              cart={cart}
              validateBeforePlaceOrder={this.validateBeforePlaceOrder}
            />
          </div>
          <div className="spc-sidebar">
            <OrderSummaryBlock
              item_qty={cart.cart.items_qty}
              items={cart.cart.items}
              totals={cart.cart.totals}
              in_stock={cart.cart.in_stock}
              cart_promo={cart.cart.cart_promo}
              show_checkout_button={false}
            />
          </div>
        </div>
        <div className="spc-post-content">
          <ConditionalView condition={window.innerWidth < 768}>
            <VatFooterText />
            {termConditions}
          </ConditionalView>
        </div>
      </>
    );
  }
}
