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
      <div className="discount">
        <div className="price--discount">
          (
          {Drupal.t('Save @discount%', { '@discount': discount })}
          )
        </div>
      </div>
    )
    : '';

  // Display special price if from and to prices are different.
  let priceClass = 'price';
  let specialPrice = '';
  if ((priceRange.from.min !== priceRange.to.min)
    || (priceRange.from.max !== priceRange.to.max)) {
    specialPrice = (
      <div className="has--special--price">
        <PriceElement
          amount={priceRange.from.min}
          maxAmount={priceRange.from.max}
        />
      </div>
    );
    priceClass = 'special--price';
  }

  return (
    <div className="special-price-block">
      {specialPrice}
      <div className={priceClass}>
        <PriceElement amount={priceRange.to.min} maxAmount={priceRange.to.max} />
      </div>
      {discountTxt}
    </div>
  );
};

export default PriceRangeElement;
