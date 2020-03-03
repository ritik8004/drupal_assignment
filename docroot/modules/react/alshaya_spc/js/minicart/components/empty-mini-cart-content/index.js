import React from 'react';

export default class EmptyMiniCartContent extends React.Component {
  render() {
    return <a className="cart-link" href={Drupal.url('cart')} />;
  }
}
