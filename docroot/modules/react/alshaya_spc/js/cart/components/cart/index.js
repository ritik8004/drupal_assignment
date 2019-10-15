import React from 'react';

import EmptyCart from '../empty-cart';

export default class Cart extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      'wait': true,
      'cart': null
    };
  }

  render() {
      if (this.state.wait || !this.state.cart) {
        return <EmptyCart></EmptyCart>
      }

      return <h1>This is cart component</h1>
  }

}
