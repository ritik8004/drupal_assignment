import React from 'react';
import PriceElement from './PriceElement';
import { calculateDiscount } from '../../utils';

const PriceBlock = ({
  children, ...props
}) => (
  <div className="price-block">
    {
        (typeof children !== 'undefined' && children.length > 0)
          ? children
          : <PriceElement {...props} />
      }
  </div>
);

const Price = ({ price, finalPrice }) => {
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

export default Price;
