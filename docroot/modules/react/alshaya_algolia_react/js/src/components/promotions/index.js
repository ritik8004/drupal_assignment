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

const Promotions = ({promotions}) => {
  const promotionList = (promotions) ? promotions.map(promotion => <Promotion key={promotion.text} promotion={promotion} />) : '';
  if (promotionList !== '' && promotionList !== 'null') {
    return <div className="promotions">{promotionList}</div>;
  }
  return (null);
};

export default Promotions;
