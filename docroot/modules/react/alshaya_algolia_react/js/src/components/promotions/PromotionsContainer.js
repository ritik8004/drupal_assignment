import React from 'react';
import Promotion from '../promotions/Promotion';

const PromotionsContainer = ({promotions}) => {
  const promotionList = (promotions) ? promotions.map(promotion => <Promotion promotion={promotion} />) : '';
  if (promotionList !== '' && promotionList !== 'null') {
    return <div className="promotions">{promotionList}</div>;
  }
  return (null);
};

export default PromotionsContainer;
