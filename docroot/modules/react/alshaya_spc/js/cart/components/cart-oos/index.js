import React from 'react';

export default class CartOutOfStock extends React.Component {

  render() {
    const in_stock = this.props.in_stock;
    if (in_stock === false) {
      return <div>{Drupal.t('Sorry, one or more products in your basket are no longer available. Please review your basket in order to checkout securely.')}</div>
    }

    return (null);
  }

}
