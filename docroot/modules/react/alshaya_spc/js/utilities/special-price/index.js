import React from 'react';
import PriceElement from './PriceElement';
import calculateDiscount from '../price-helper';
import { getDataAttributePrices } from '../../../../js/utilities/price';

const PriceBlock = ({ children, amount, fixedPrice = '' }) => (
  <div className="price-block">
    {
        (typeof children !== 'undefined' && children.length > 0)
          ? children
          : <PriceElement amount={amount} fixedPrice={fixedPrice} />
      }
  </div>
);

const SpecialPrice = ({
  price,
  finalPrice,
  freeItem,
  fixedPrice = '',
}) => {
  // If freeItem is true, we just want to display text "Free".
  if (freeItem) return Drupal.t('Free');

  let priceVal = price;
  let finalPriceVal = finalPrice;
  if (priceVal !== undefined && priceVal !== null) {
    // Remove the comma and convert to float.
    priceVal = parseFloat(priceVal.toString().replace(',', ''));
  }

  if (finalPriceVal !== undefined && finalPriceVal !== null) {
    // Remove the comma and convert to float.
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
          <PriceElement amount={priceVal} fixedPrice={getDataAttributePrices(fixedPrice, 'price')} />
        </div>
        <div className="special--price">
          <PriceElement amount={finalPriceVal} fixedPrice={getDataAttributePrices(fixedPrice, 'special_price')} />
        </div>
        {discountTxt}
      </PriceBlock>
    );
  }
  if (finalPriceVal) {
    return <PriceBlock amount={finalPriceVal} fixedPrice={getDataAttributePrices(fixedPrice, 'price')} />;
  }

  return <PriceBlock amount={priceVal} fixedPrice={getDataAttributePrices(fixedPrice, 'price')} />;
};

export default SpecialPrice;
