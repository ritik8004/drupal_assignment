import React from 'react';
import PriceElement from './PriceElement';
import { calculateDiscount } from '../../utils';
import { getDataAttributePrices } from '../../utils/PriceHelper';

const PriceBlock = ({
  children, ...props
}) => {
  const { fixedPrice } = props;
  return (
    <div className="price-block" data-fp={fixedPrice}>
      {
        (typeof children !== 'undefined' && React.isValidElement(children))
          ? children
          : <PriceElement {...props} />
      }
    </div>
  );
};

const Price = ({ price, finalPrice, fixedPrice = '' }) => {
  // Set finalPrice from sku.
  let finalPriceValue = finalPrice;

  // Get cross border fixed price string for data-fp attribute on price div.
  const fixedPriceJsonString = getDataAttributePrices(fixedPrice, 'price');

  // Get cross border special price string for data-fp attribute on price div.
  const specialPriceJsonString = getDataAttributePrices(fixedPrice, 'special_price');

  // This is work around for cross border.
  if (specialPriceJsonString) {
    // If Sku has special price value in fixed_price attribute for any
    // currency, then render price with discount by setting final_price if not
    // set from backend.
    finalPriceValue = (finalPriceValue && price > finalPriceValue) ? finalPriceValue : 0.01;
  }

  if (price > 0 && finalPrice > 0 && finalPrice < price) {
    const discount = calculateDiscount(price, finalPriceValue);
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
        <div className="special-price-block">
          <div className="has--special--price">
            <PriceElement amount={price} fixedPrice={fixedPriceJsonString} />
          </div>
          <div className="special--price">
            <PriceElement amount={finalPriceValue} fixedPrice={specialPriceJsonString} />
          </div>
          {discountTxt}
        </div>
      </PriceBlock>
    );
  }
  if (finalPriceValue) {
    return <PriceBlock amount={finalPriceValue} fixedPrice={fixedPriceJsonString} />;
  }

  return <PriceBlock amount={price} fixedPrice={fixedPriceJsonString} />;
};

export default Price;
