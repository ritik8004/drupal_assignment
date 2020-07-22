import React from 'react';
import PriceElement from './PriceElement';
import calculateDiscount from '../price-helper';

const PriceBlock = ({ children, amount }) => (
  <div className="price-block">
    {
        (typeof children !== 'undefined' && children.length > 0)
          ? children
          : <PriceElement amount={amount} />
      }
  </div>
);

const SpecialPrice = ({ price, finalPrice }) => {
  let priceVal = price;
  let finalPriceVal = finalPrice;
  if (priceVal !== undefined && priceVal !== null) {
    // Remove and command and convert to float.
    priceVal = parseFloat(priceVal.toString().replace(',', ''));
  }

  if (finalPriceVal !== undefined && finalPriceVal !== null) {
    // Remove and command and convert to float.
    finalPriceVal = parseFloat(finalPriceVal.toString().replace(',', ''));
  }

  if (priceVal > 0 && finalPriceVal > 0 && finalPriceVal < priceVal) {
    const discount = calculateDiscount(priceVal, finalPriceVal);
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
          <PriceElement amount={priceVal} />
        </div>
        <div className="special--price">
          <PriceElement amount={finalPriceVal} />
        </div>
        {discountTxt}
      </PriceBlock>
    );
  }
  if (finalPriceVal) {
    return <PriceBlock amount={finalPriceVal} />;
  }

  return <PriceBlock amount={priceVal} />;
};

export default SpecialPrice;
