import React from 'react';

export default class CartShoppingBag extends React.Component {

  render() {
    return <div>{Drupal.t('My Shopping Bag (@qty items)', {'@qty': this.props.qty})}</div>
  }

}
