import React from 'react';
import parse from 'html-react-parser';

const OrderSummaryItem = (props) => {
  const { type, label, value } = props;

  if (type === 'address') {
    const { name, address } = props;
    return (
      <div className="spc-order-summary-item spc-order-summary-address-item">
        <span className="spc-label">{`${label}:`}</span>
        <span className="spc-value">
          <span className="spc-address-name">
            {name}
          </span>
          <span className="spc-address">
            {address}
          </span>
        </span>
      </div>
    );
  }

  if (type === 'cnc') {
    const { name, address, timings } = props;
    const storeTimings = timings.split('\n').map((item) => <p>{item}</p>);
    return (
      <div className="spc-order-summary-item spc-order-summary-address-item">
        <span className="spc-label">{`${label}:`}</span>
        <span className="spc-value">
          <span className="spc-address-name">
            {name}
          </span>
          <span className="spc-address">
            {address}
          </span>
          <span className="spc-address">
            {storeTimings}
          </span>
        </span>
      </div>
    );
  }

  if (type === 'markup') {
    return (
      <div className="spc-order-summary-item spc-order-summary-markup-item">
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
};

export default OrderSummaryItem;
