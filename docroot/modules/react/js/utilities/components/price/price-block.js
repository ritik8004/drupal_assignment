import React from 'react';
import PriceElement from './price-element';

const PriceBlock = ({ children, ...props }) => {
  const { fixedPrice } = props;
  return (
    <div className="price-block" data-fp={fixedPrice}>
      {typeof children !== 'undefined' && children.length > 0 ? (
        children
      ) : (
        <PriceElement {...props} />
      )}
    </div>
  );
};

export default PriceBlock;
