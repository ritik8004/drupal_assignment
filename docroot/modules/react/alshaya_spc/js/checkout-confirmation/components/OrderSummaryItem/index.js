import React from 'react';

class OrderSummaryItem extends React.Component {
  render() {
    if (this.props.type === 'address') {
      return (
        <div className='spc-order-summary-item spc-order-summary-address-item'>
          <span className='spc-label'>{this.props.label + ':'}</span>
          <span className='spc-value'>
            <span className='spc-address-name'>
              {this.props.name}
            </span>
            <span className='spc-address'>
              {this.props.address}
            </span>
          </span>
        </div>
      );
    }
    else {
      return (
        <div className='spc-order-summary-item'>
          <span className='spc-label'>{this.props.label + ':'}</span>
          <span className='spc-value'>{this.props.value}</span>
        </div>
      );
    }
  }
}

export default OrderSummaryItem;
