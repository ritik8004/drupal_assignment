import React from 'react';

export default class FreeDeliveryText extends React.Component {

  render() {
    if (!this.props.freeDelivery) {
      return <span className="delivery-prefix">{this.props.text}</span>
    }

    return <span className="delivery-prefix"/>;
  }

}
