import React from 'react';
import PriceElement from './price-element';

const PriceBlock = ({ children, ...props }) => (
  <div className="price-block">
    {typeof children !== 'undefined' && children.length > 0 ? (
      children
    ) : (
      <PriceElement {...props} />
    )}
  </div>
);

export default PriceBlock;
