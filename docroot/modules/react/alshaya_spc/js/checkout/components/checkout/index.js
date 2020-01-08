import React from 'react';

import EmptyResult from '../../../utilities/empty-result';
import Loading from '../../../utilities/loading';
import {fetchCartData} from '../../../utilities/get_cart';
import DeliveryMethods from '../delivery-methods';
import DeliveryInformation from '../delivery-information';
import PaymentMethods from '../payment-methods';
import CompletePurchase from '../complete-purchase';
import OrderSummaryBlock from '../../../utilities/order-summary-block';
import TermsConditions from '../terms-conditions';
import CheckoutMessage from "../../../utilities/checkout-message";
import MobileCartPreview from "../../../cart/components/mobile-cart-preview";
import SectionTitle from "../../../utilities/section-title";
import CartItems from "../../../cart/components/cart-items";
import VatFooterText from "../../../utilities/vat-footer";
import CartPromoBlock from "../../../cart/components/cart-promo-block";
import CartRecommendedProducts
  from "../../../cart/components/recommended-products";

export default class Checkout extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      'wait': true,
      'cart': null,
    };
  }

  componentDidMount() {
    try {
      // Fetch cart data.
      var cart_data = fetchCartData();
      if (cart_data instanceof Promise) {
        cart_data.then((result) => {
            this.setState({
            wait: false,
            cart: result
          });
        });
      }
    }
    catch(error) {
      // In case of error, do nothing.
    }
  }

  render() {
      // While page loads and all info available.
      if (this.state.wait) {
        return <Loading loadingMessage={Drupal.t('loading...')}/>
      }

      // If cart not available.
      if (this.state.cart === null) {
        return (
          <React.Fragment>
            <EmptyResult Message={Drupal.t('your shopping basket is empty.')}/>
          </React.Fragment>
        );
      }

      return (
        <React.Fragment>
          <div className="spc-pre-content"/>
          <div className="spc-main">
            <div className="spc-content">
              <DeliveryMethods cnc_disabled={!this.state.cart.cnc_enabled} delivery_type={this.state.cart.delivery_method} />
              <DeliveryInformation delivery_type={this.state.cart.delivery_method} />
              <PaymentMethods cart={this.state.cart} is_active={false} />
              <TermsConditions/>
              <CompletePurchase enable={false}/>
            </div>
            <div className="spc-sidebar">
              <OrderSummaryBlock items={this.state.cart.items} totals={this.state.cart.totals} in_stock={this.state.cart.in_stock} cart_promo={this.state.cart.cart_promo} show_checkout_button={false} />
            </div>
          </div>
          <div className="spc-post-content"/>
        </React.Fragment>
      );
  }

}
