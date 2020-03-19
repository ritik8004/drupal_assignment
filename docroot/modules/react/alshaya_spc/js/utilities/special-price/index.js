import React from 'react';
import PriceElement from './PriceElement';
import { calculateDiscount } from '../price-helper';

const PriceBlock = (props) => (
  <div className="price-block">
    {
        (typeof props.children !== 'undefined' && props.children.length > 0)
          ? props.children
          : <PriceElement {...props} />
      }
  </div>
);

const SpecialPrice = ({ price, finalPrice }) => {
  if (price > 0 && finalPrice > 0 && finalPrice < price) {
    const discount = calculateDiscount(price, finalPrice);
    const discountTxt = (discount > 0)
      ? (
        <div className="price--discount">
          (
          {Drupal.t('Save @discount%', { '@discount': discount })}
          )
        </div>
      )
      : '';

    return (
      <PriceBlock>
        <div className="has--special--price">
          <PriceElement amount={price} />
        </div>
        <div className="special--price">
          <PriceElement amount={finalPrice} />
        </div>
        {discountTxt}
      </PriceBlock>
    );
  }
  if (finalPrice) {
    return <PriceBlock amount={finalPrice} />;
  }

  return <PriceBlock amount={price} />;
};

export default SpecialPrice;
