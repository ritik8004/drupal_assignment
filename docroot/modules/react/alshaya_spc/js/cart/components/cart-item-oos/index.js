import React from 'react';

export default class CartItemOOS extends React.Component {
  render() {
    const {
      inStock,
    } = this.props;

    if (inStock !== true) {
      return <div>{Drupal.t('This product is out of stock. Please remove to proceed.')}</div>;
    }
    return (null);
  }
}
