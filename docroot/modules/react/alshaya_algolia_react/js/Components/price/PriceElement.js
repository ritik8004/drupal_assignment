import React from 'react';

const PriceElement = (props) => {
  return (
    <div className="price-type__wrapper">
      <div className="price">
        <span className="price-wrapper">
          <div className="price">
            <span className="price-currency suffix">KWD</span>
            <span className="price-amount">{props.amount}</span>
          </div>
        </span>
      </div>
    </div>
  );
};

export default PriceElement;