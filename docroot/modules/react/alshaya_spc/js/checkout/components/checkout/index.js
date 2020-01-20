import React from 'react';

import EmptyResult from '../../../utilities/empty-result';
import Loading from '../../../utilities/loading';
import {fetchCartData} from '../../../utilities/get_cart';
import {getPaymentMethods} from '../../../utilities/checkout_util';
import DeliveryMethods from '../delivery-methods';
import DeliveryInformation from '../delivery-information';
import PaymentMethods from '../payment-methods';
import CompletePurchase from '../complete-purchase';
import OrderSummaryBlock from '../../../utilities/order-summary-block';
import TermsConditions from '../terms-conditions';
import { stickySidebar } from "../../../utilities/stickyElements/stickyElements";
import {addInfoInStorage} from '../../../utilities/storage';
import {addPaymentMethodInCart} from '../../../utilities/update_cart';

export default class Checkout extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      'wait': true,
      'cart': null,
      'delivery_method': 'hd',
      'payment_active': false,
      'shipping_method': null,
      'payment_methods': window.drupalSettings.payment_methods,
      'selected_payment_method': null
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
 
            if (result.delivery_method !== undefined) {
              this.setState({
                delivery_method: result.delivery_method,
              });
            }
        });
      }
    }
    catch(error) {
      // In case of error, do nothing.
    }

    // Make sidebar sticky.
    stickySidebar();
  }

  // On delivery method change.
  updateDeliveryMethod = (method) => {
    this.setState({
      delivery_method: method
    });
  }

  onPaymentMethodSelect = (method) => {
    this.setState({
      selected_payment_method: method
    });

    // Prepare payment data.
    // For now we assuming only very basic payment methods.
    let payment_data = {
      'payment': {
        'method': method,
        'additional_data': {
          'cc_type': ''
        }
      }
    };
    var cart_data = addPaymentMethodInCart('update payment', payment_data);
    if (cart_data instanceof Promise) {
      cart_data.then((result) => {
          this.setState({
            cart: result,
          });

          addInfoInStorage(result);
      });
    }
  }

  refreshCart = (cart) => {
    this.setState({
      cart: cart
    });

    addInfoInStorage(cart);
  }

  // Refresh payment methods list.
  refreshPaymentMethods = () => {
    let payment_methods = getPaymentMethods(this.state.cart.cart_id);
    if (payment_methods instanceof Promise) {
      payment_methods.then((result) => {
        let methods = new Array();
        Object.entries(result).forEach(([key, method]) => {
          if (window.drupalSettings.payment_methods[method.code] !== undefined) {
            methods[method.code] = window.drupalSettings.payment_methods[method.code];
          }
        });

        this.setState({
          payment_methods: methods,
          payment_active: true
        });
    });
    }
  }

  render() {
      // While page loads and all info available.
      if (this.state.wait) {
        return <Loading loadingMessage={Drupal.t('loading checkout ...')}/>
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
              <DeliveryMethods cnc_disabled={!this.state.cart.cnc_enabled} delivery_type={this.state.delivery_method} updateMethod={this.updateDeliveryMethod} />
              <DeliveryInformation refreshCart={this.refreshCart} paymentMethodRefresh={this.refreshPaymentMethods} cart={this.state.cart} delivery_type={this.state.delivery_method} />
              <PaymentMethods selected_payment_method={this.state.selected_payment_method} payment_methods={this.state.payment_methods} cart={this.state.cart} is_active={this.state.payment_active} payment_method_select={this.onPaymentMethodSelect} />
              {window.innerWidth > 768 &&
                <TermsConditions/>
              }
              <CompletePurchase cart={this.state.cart} selected_payment_method={this.state.selected_payment_method}/>
            </div>
            <div className="spc-sidebar">
              <OrderSummaryBlock item_qty={this.state.cart.items_qty} items={this.state.cart.items} totals={this.state.cart.totals} in_stock={this.state.cart.in_stock} cart_promo={this.state.cart.cart_promo} show_checkout_button={false} />
            </div>
          </div>
          <div className="spc-post-content">
          {window.innerWidth < 768 &&
            <TermsConditions/>
          }
          </div>
        </React.Fragment>
      );
  }

}
