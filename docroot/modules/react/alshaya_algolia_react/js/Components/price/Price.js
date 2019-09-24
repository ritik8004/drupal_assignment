import React from 'react';
import PriceElement from './PriceElement';

const Price = (props) => {
  return (
    <div className="price-block price-block-_716958002">
      <div className="has--special--price">
        <PriceElement amount="7.990"/>
      </div>
      <div className="special--price">
        <PriceElement amount="4.000"/>
      </div>
      <div className="price--discount">
        (save 50%)
      </div>
    </div>
  );
};

export default Price;