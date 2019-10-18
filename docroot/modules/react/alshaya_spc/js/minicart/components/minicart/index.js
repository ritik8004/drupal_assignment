import React from 'react';

import 'promise-polyfill/src/polyfill';
import {fetchCartData} from '../../../utilities/get_cart';
import {addInfoInStorage} from '../../../utilities/storage';
import EmptyMiniCart from '../empty-mini-cart';
import Price from '../../../utilities/price';

export default class MiniCart extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      'qty': null,
      'amount': null,
      'wait': true
    };
  }

  componentDidMount() {
    try {
      var cart_data = fetchCartData();
      if (cart_data instanceof Promise) {
          cart_data.then((result) => {
            this.setState({
            wait: false,
            qty: result.items_qty,
            amount: result.cart_total
          });

          // Store info in storage.
          addInfoInStorage(result);

          // Trigger event so that data can be passed to other components.
          var event = new CustomEvent('refreshCart', {bubbles: true, detail: { data: () => result }});
          document.dispatchEvent(event);

        });
      }

      // Listen to `refreshMiniCart` event which will update/refresh the minicart from
      // PDP item add or from the update from cart page.
      document.addEventListener('refreshMiniCart', (e) => {
        var data = e.detail.data();
        this.setState({
          qty: data.items_qty,
          amount: data.cart_total,
          wait: false
        });

        // Store info in storage.
        addInfoInStorage(data);
      }, false);
    } catch (error) {
      // In case of error, do nothing.
    }
  }

  render() {
    if (this.state.wait) {
      return <EmptyMiniCart></EmptyMiniCart>
    }

    return <div>
        <Price price={this.state.amount}></Price>
        <span>{this.state.qty}</span>
      </div>
  }

}
