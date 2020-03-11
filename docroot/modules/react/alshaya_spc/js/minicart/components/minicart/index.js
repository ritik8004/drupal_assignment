import React from 'react';

import 'promise-polyfill/src/polyfill';
import { fetchCartData } from '../../../utilities/get_cart';
import { addInfoInStorage } from '../../../utilities/storage';
import EmptyMiniCartContent from '../empty-mini-cart-content';
import MiniCartContent from "../mini-cart-content";
import { checkCartCustomer } from '../../../utilities/cart_customer_util';

export default class MiniCart extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      'qty': null,
      'amount': null,
      'wait': true
    };
    this.emptyResult = { cart_id: null };
  }

  dispatchRefereshCart = (result) => {
    // Trigger event so that data can be passed to other components.
    var event = new CustomEvent('refreshCart', { bubbles: true, detail: { data: () => result } });
    document.dispatchEvent(event);
    checkCartCustomer(result);
  }

  componentDidMount() {
    try {
      var cart_data = fetchCartData();
      if (cart_data instanceof Promise) {
        cart_data.then((result) => {

          if (typeof result === 'undefined') {
            result = this.emptyResult;
          }
          else if (typeof result.error !== 'undefined' && result.error) {
            this.setState({
              wait: false,
              qty: null,
              amount: null
            });

            result = this.emptyResult;
          }
          else {
            this.setState({
              wait: false,
              qty: result.items_qty,
              amount: result.cart_total
            });
          }

          // Store info in storage.
          let data_to_store = {
            'cart': result
          };
          addInfoInStorage(data_to_store);

          // Trigger event so that data can be passed to other components.
          this.dispatchRefereshCart(result);
        });
      }
      else {
        // Trigger event so that data can be passed to other components.
        this.dispatchRefereshCart(this.emptyResult);
      }

      // Listen to `refreshMiniCart` event which will update/refresh the minicart from
      // PDP item add or from the update from cart page.
      document.addEventListener('refreshMiniCart', (e) => {
        var data = e.detail.data();
        // If no error from MDC.
        if (data && data.error === undefined) {
          this.setState({
            qty: data.items_qty,
            amount: data.cart_total,
            wait: false
          });

          // Store info in storage.
          let data_to_store = {
            'cart': data
          };
          addInfoInStorage(data_to_store);

          if (data.items.length === 0) {
            this.setState({
              wait: true
            });
          }
          checkCartCustomer(data);
        }
      }, false);
    } catch (error) {
      // In case of error, do nothing.
    }
  }

  render() {
    if (this.state.wait || !this.state.qty) {
      return <div className="acq-mini-cart">
        <EmptyMiniCartContent />
      </div>
    }

    return <div className="acq-mini-cart">
      <MiniCartContent amount={this.state.amount} qty={this.state.qty} />
    </div>
  }

}
