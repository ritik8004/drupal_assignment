import React from 'react';

import 'promise-polyfill/src/polyfill';
import { fetchCartData } from '../../../utilities/api/requests';
import { addInfoInStorage } from '../../../utilities/storage';
import EmptyMiniCartContent from '../empty-mini-cart-content';
import MiniCartContent from '../mini-cart-content';
import { checkCartCustomer } from '../../../utilities/cart_customer_util';

export default class MiniCart extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      qty: null,
      amount: null,
      wait: true,
    };
    this.emptyResult = { cart_id: null };
  }

  componentDidMount() {
    try {
      const cartData = fetchCartData();
      if (cartData instanceof Promise) {
        cartData.then((result) => {
          let resultVal = result;
          if (typeof resultVal === 'undefined') {
            resultVal = this.emptyResult;
          } else if (typeof resultVal.error !== 'undefined' && resultVal.error) {
            this.setState({
              wait: false,
              qty: null,
              amount: null,
            });

            resultVal = this.emptyResult;
          } else {
            this.setState({
              wait: false,
              qty: resultVal.items_qty,
              amount: resultVal.cart_total,
            });
          }

          // Store info in storage.
          const dataToStore = {
            cart: resultVal,
          };
          addInfoInStorage(dataToStore);

          // Trigger event so that data can be passed to other components.
          this.dispatchRefereshCart(resultVal);
        });
      } else {
        // Trigger event so that data can be passed to other components.
        this.dispatchRefereshCart(this.emptyResult);
      }

      // Listen to `refreshMiniCart` event which will update/refresh the minicart from
      // PDP item add or from the update from cart page.
      document.addEventListener('refreshMiniCart', (e) => {
        const data = e.detail.data();
        // If no error from MDC.
        if (data && data.error === undefined) {
          this.setState({
            qty: data.items_qty,
            amount: data.cart_total,
            wait: false,
          });

          // Store info in storage.
          const dataToStore = {
            cart: data,
          };
          addInfoInStorage(dataToStore);

          if (data.items.length === 0) {
            this.setState({
              wait: true,
            });
          }
          checkCartCustomer(data);
        }
      }, false);
    } catch (error) {
      // In case of error, do nothing.
    }
  }

  dispatchRefereshCart = (result) => {
    // Trigger event so that data can be passed to other components.
    const event = new CustomEvent('refreshCart', { bubbles: true, detail: { data: () => result } });
    document.dispatchEvent(event);
    checkCartCustomer(result);
  }

  render() {
    const { wait, qty, amount } = this.state;
    if (wait || !qty) {
      return (
        <div className="acq-mini-cart">
          <EmptyMiniCartContent />
        </div>
      );
    }

    return (
      <div className="acq-mini-cart">
        <MiniCartContent amount={amount} qty={qty} />
      </div>
    );
  }
}
