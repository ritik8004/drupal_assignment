import React from 'react';

import EmptyCart from '../empty-cart';
import CartTotalSubTotal from '../cart-total-subtotal';
import CartOutOfStock from '../cart-oos';

export default class Cart extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      'wait': true,
      'items': [],
      'totals': [],
      'recommended_products': [],
      'total_items': null,
      'in_stock': true
    };
  }

  componentDidMount() {
    // Listen to `refreshCart` event triggered from `mini-cart/index.js`.
    document.addEventListener('refreshCart', (e) => {
      const data = e.detail.data();
      var in_stock = true;

      Object.keys(data.items).forEach(function(key) {
        if (data.items[key].stock === 0) {
          in_stock = false;
        }
      })

      this.setState(state => ({
        items: data.items,
        totals: data.totals,
        recommended_products: { },
        total_items: data.items_qty,
        wait: false,
        in_stock: in_stock
      }));
    }, false);
  };

  render() {
      if (this.state.wait) {
        return <EmptyCart></EmptyCart>
      }

      return (
        <div>
          <CartOutOfStock in_stock={this.state.in_stock} />
          <CartTotalSubTotal totals={this.state.totals} in_stock={this.state.in_stock}></CartTotalSubTotal>
        </div>
      );
  }

}
