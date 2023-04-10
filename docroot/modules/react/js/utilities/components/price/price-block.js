import React from 'react';
import PriceElement from './price-element';
import { hasValue } from '../../conditionsUtility';

const PriceBlock = ({ children, ...props }) => {
  const { fixedPrice, sku } = props;
  return (
    <div className="price-block" data-fp={fixedPrice}>
      {hasValue(children) ? (
        children
      ) : (
        <PriceElement key={sku} {...props} />
      )}
    </div>
  );
};

export default PriceBlock;
