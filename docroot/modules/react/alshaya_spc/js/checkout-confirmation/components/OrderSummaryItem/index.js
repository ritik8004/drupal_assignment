import React from 'react';
import parse from 'html-react-parser';

class OrderSummaryItem extends React.Component {
  render() {
    const { type, label, value } = this.props;

    if (type === 'address') {
      const { name, address } = this.props;
      return (
        <div className="spc-order-summary-item spc-order-summary-address-item">
          <span className="spc-label">{`${this.props.label}:`}</span>
          <span className="spc-value">
            <span className="spc-address-name">
              {this.props.name}
            </span>
            <span className="spc-address">
              {this.props.address}
            </span>
          </span>
        </div>
      );
    }

    if (type === 'markup') {
      return (
        <div className="spc-order-summary-item">
          <span className="spc-label">{`${label}:`}</span>
          <span className="spc-value">{parse(value)}</span>
        </div>
      );
    }

    return (
      <div className="spc-order-summary-item">
        <span className="spc-label">{`${label}:`}</span>
        <span className="spc-value">{value}</span>
      </div>
    );
  }
}

export default OrderSummaryItem;
