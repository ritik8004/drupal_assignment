import React from 'react';

export default class ItemLowQuantity extends React.Component {

  render() {
    if (this.props.in_stock && this.props.stock < this.props.qty) {
      return <div>{Drupal.t('This product is not available in selected quantity. Please adjust the quantity to proceed.')}</div>
    }

    return (null);
  }

}
