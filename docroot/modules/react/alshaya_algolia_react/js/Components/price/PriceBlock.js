import React from 'react';
import PriceElement from './PriceElement';

const PriceBlock = props => {
  return (
    <div className="price-block">
      {
        (typeof props.children != 'undefined' && props.children.length > 0)
          ? props.children
          : <PriceElement {...props} />
      }
    </div>
  );
}

export default PriceBlock;
