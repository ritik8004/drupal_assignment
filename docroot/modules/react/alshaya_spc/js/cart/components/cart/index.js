import React from 'react';

import EmptyCart from '../empty-cart';
import CartShoppingBag from '../cart-shopping-bag';
import CartTotalSubTotal from '../cart-total-subtotal';
import CartOutOfStock from '../cart-oos';
import CartRecommendedProducts from '../recommended-products';

export default class Cart extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      'wait': true,
      'items': [],
      'totals': [],
      'recommended_products': [],
      'total_items': null,
      'amount': null,
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
        recommended_products: data.recommended_products,
        total_items: data.items_qty,
        amount: data.cart_total,
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
          <CartShoppingBag qty={this.state.total_items} total={this.state.amount} />
          <CartTotalSubTotal totals={this.state.totals} in_stock={this.state.in_stock}></CartTotalSubTotal>
          <CartRecommendedProducts recommended_products={this.state.recommended_products} />
        </div>
      );
  }

}
