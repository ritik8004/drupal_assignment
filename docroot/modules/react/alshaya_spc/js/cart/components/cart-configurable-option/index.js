import React from 'react';

export default class CartConfigurableOption extends React.Component {

  render() {
    return <div>
      <span>{this.props.label.label}</span>
      <span>{this.props.label.value}</span>
    </div>
  }

}
