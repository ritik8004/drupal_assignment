import React from 'react';

import CartItem from '../cart-item';

export default class CartItems extends React.Component {

  render() {
    const products = this.props.items;
    let productItems = [];
    Object.entries(products).forEach(([key, product]) => {
      productItems.push(<CartItem key={key} item={product} />);
      productItems.push(<CartItem key={key} item={product} />);
    });

    return (
    <React.Fragment>
      <div className="spc-cart-items">{productItems}</div>
    </React.Fragment>
    );
  }

}
