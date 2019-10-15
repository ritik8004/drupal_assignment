import React from 'react';

import EmptyCart from '../empty-cart';

export default class Cart extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      'wait': true,
      'items': [],
      'totals': [],
      'recommended_products': [],
      'total_items': null
    };
  }

  componentDidMount() {
    // Listen to `refreshCart` event triggered from `mini-cart/index.js`.
    document.addEventListener('refreshCart', (e) => {
      var data = e.detail.data();
      this.setState(state => ({
        items: { },
        totals: data.totals,
        recommended_products: { },
        total_items: data.items_qty,
        wait: false
      }));
    }, false);
  };

  render() {
      if (this.state.wait) {
        return <EmptyCart></EmptyCart>
      }

      return <h1>This is cart component</h1>
  }

}
