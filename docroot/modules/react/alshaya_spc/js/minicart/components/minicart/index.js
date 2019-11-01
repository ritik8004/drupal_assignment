import React from 'react';

import 'promise-polyfill/src/polyfill';
import {fetchCartData} from '../../../utilities/get_cart';
import {addInfoInStorage} from '../../../utilities/storage';
import EmptyMiniCartContent from '../empty-mini-cart-content';
import MiniCartContent from "../mini-cart-content";

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

          var in_stock = true;
          Object.keys(result.items).forEach(function(key) {
            if (result.items[key].stock === 0) {
              in_stock = false;
            }
          });
          result.in_stock = in_stock;

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
      return <div className="acq-mini-cart">
        <EmptyMiniCartContent/>
      </div>
    }

    return <div className="acq-mini-cart">
      <MiniCartContent amount={this.state.amount} qty={this.state.qty}/>
      </div>
  }

}
