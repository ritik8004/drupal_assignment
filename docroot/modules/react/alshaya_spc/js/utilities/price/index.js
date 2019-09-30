import React from 'react';

export default class Price extends React.Component {

  render() {
    const price = this.props.price.toFixed(this.props.decimal_position);
    return <span>{price}</span>
  }

}
