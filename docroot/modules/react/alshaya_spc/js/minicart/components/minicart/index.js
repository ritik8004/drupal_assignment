import React from 'react';

import 'promise-polyfill/src/polyfill';
import { fetchCartData } from '../../../utilities/api/requests';
import EmptyMiniCartContent from '../empty-mini-cart-content';
import MiniCartContent from '../mini-cart-content';
import { checkCartCustomer } from '../../../utilities/cart_customer_util';
import dispatchCustomEvent from '../../../utilities/events';
import '../../../utilities/interceptor/interceptor';

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
          if (result === 'Request aborted') {
            return;
          }

          let resultVal = result;
          if (typeof resultVal === 'undefined' || resultVal === null) {
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
              amount: resultVal.minicart_total,
            });
          }
          // Dispatch a custom event to let other modules do miscellaneous
          // check. i.e. qty limit
          dispatchCustomEvent('cartMiscCheck', resultVal);
          // Store info in storage.
          const dataToStore = {
            cart: resultVal,
          };
          window.commerceBackend.setCartData(dataToStore);

          // Trigger event so that data can be passed to other components.
          this.dispatchRefereshCart(resultVal);
        });
      } else if (cartData === 'Request aborted') {
        // Request is aborted, user seems to have refreshed the page.
        return;
      } else {
        // Trigger event so that data can be passed to other components.
        this.dispatchRefereshCart(this.emptyResult);
      }

      // Listen to `refreshMiniCart` event which will update/refresh the minicart from
      // PDP item add or from the update from cart page.
      document.addEventListener('refreshMiniCart', (e) => {
        const data = e.detail.data();

        dispatchCustomEvent('cartMiscCheck', {
          data,
          productData: e.detail.productData !== undefined ? e.detail.productData : '',
        });

        // If no error from MDC.
        if (data && data.error === undefined) {
          this.setState({
            qty: data.items_qty,
            amount: data.minicart_total,
            wait: false,
          });

          // Store info in storage.
          const dataToStore = {
            cart: data,
          };
          window.commerceBackend.setCartData(dataToStore);

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
