import React from 'react';
import PriceElement from './PriceElement';
import { calculateDiscount } from '../../utils';

/**
 * Render price range elements.
 */
const PriceRangeElement = ({ priceRange }) => {
  const discount = calculateDiscount(priceRange.from.min, priceRange.to.min);
  const discountTxt = (discount > 0)
    ? (
      <div className="price--discount">
        (
        {Drupal.t('Save @discount%', { '@discount': discount })}
        )
      </div>
    )
    : '';

  // Display special price if from and to prices are different.
  const specialPrice = (
    (priceRange.from.min !== priceRange.to.min)
    || (priceRange.from.max !== priceRange.to.max)
  )
    ? (
      <PriceElement
        amount={priceRange.from.min}
        maxAmount={priceRange.from.max}
      />
    )
    : '';
  return (
    <div className="special-price-block">
      <div className="has--special--price">
        {specialPrice}
      </div>
      <div className="special--price">
        <PriceElement amount={priceRange.to.min} maxAmount={priceRange.to.max} />
      </div>
      <div className="discount">{discountTxt}</div>
    </div>
  );
};

export default PriceRangeElement;
