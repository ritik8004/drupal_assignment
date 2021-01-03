import React from 'react';

const Promotion = ({ promotion }) => (
  <span className="sku-promotion-item">
    <a className="sku-promotion-link" href={promotion[`url_${drupalSettings.path.currentLanguage}`]}>
      {promotion.text}
    </a>
  </span>
);

const Promotions = ({ promotions }) => {
  const promotionList = (promotions)
    ? promotions.map((promotion) => {
      if (!promotion.context.length || promotion.context.includes('web') || typeof promotion.context === 'undefined') {
        return <Promotion key={promotion.text} promotion={promotion} />;
      }
      return '';
    })
    : '';
  if (promotionList !== '' && promotionList !== 'null') {
    return <div className="promotions">{promotionList}</div>;
  }
  return (null);
};

export default Promotions;
