import React from 'react';
import PriceElement from './PriceElement';
import { calculateDiscount } from '../../utils';

const PriceRangeElement = ({ alshayaPriceRange }) => {
  const discount = calculateDiscount(alshayaPriceRange.from.min, alshayaPriceRange.to.min);
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
    (alshayaPriceRange.from.min !== alshayaPriceRange.to.min)
    || (alshayaPriceRange.from.max !== alshayaPriceRange.to.max)
  )
    ? (
      <PriceElement
        amount={alshayaPriceRange.from.min}
        maxAmount={alshayaPriceRange.from.max}
      />
    )
    : '';
  return (
    <div className="special-price-block">
      <div className="has--special--price">
        {specialPrice}
      </div>
      <div className="special--price">
        <PriceElement amount={alshayaPriceRange.to.min} maxAmount={alshayaPriceRange.to.max} />
      </div>
      <div className="discount">{discountTxt}</div>
    </div>
  );
};

export default PriceRangeElement;
