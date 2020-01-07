import React from 'react';

import CheckoutCartItem from '../checkout-cart-item';

export default class CheckoutCartItems extends React.Component {

  render() {
    const products = this.props.items;
    let productItems = [];
    Object.entries(products).forEach(([key, product]) => {
      productItems.push(<CheckoutCartItem key={key} item={product} />);
    });

    return (
      <div>{productItems}</div>
    );
  }

}
