import React from 'react';

import 'promise-polyfill/src/polyfill';
import {getCartCookie, cartAvailableInStorage, fetchCartData} from '../../../utilities/get_cart';
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
    } catch (error) {
      // In case of error, do nothing.
    }
  }

  render() {
    const currency_config = window.drupalSettings.alshaya_spc.currency_config;
    if (this.state.wait) {
      return <EmptyMiniCart></EmptyMiniCart>
    }

    return <div>
        <span>{currency_config.currency_code}</span>
        <Price price={this.state.amount} decimal_position={currency_config.decimal_points}></Price>
        <span>{this.state.qty}</span>
      </div>
  }

}
