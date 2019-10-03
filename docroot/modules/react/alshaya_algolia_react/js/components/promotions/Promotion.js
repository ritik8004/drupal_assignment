import React from 'react';

const Promotion = ({promotion}) => {
  return (
    <span className="sku-promotion-item">
      <a className="sku-promotion-link" href={promotion.url}>
        {promotion.text}
      </a>
    </span>
  );
};

export default Promotion;