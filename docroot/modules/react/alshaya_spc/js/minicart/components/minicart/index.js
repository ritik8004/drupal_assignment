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
            amount: result.total
          });

          // Store info in storage.
          addInfoInStorage(result);
        });
      }
    } catch (error) {
      // In case of error, do nothing.
    }
  }

  render() {
    const drupal_settings = window.drupalSettings.mini_cart;
    if (this.state.wait) {
      return <EmptyMiniCart></EmptyMiniCart>
    }

    return <div>
        <span>{drupal_settings.currency_code}</span>
        <Price price={this.state.amount} decimal_position={drupal_settings.decimal_points}></Price>
        <span>{this.state.qty}</span>
      </div>
  }

}
