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
        <div className="special-price-block">
          <div className="has--special--price">
            <PriceElement amount={price} fixedPrice={getDataAttributePrices(fixedPrice, 'special_price')} />
          </div>
          <div className="special--price">
            <PriceElement amount={finalPrice} fixedPrice={getDataAttributePrices(fixedPrice, 'special_price')} />
          </div>
          {discountTxt}
        </div>
      </PriceBlock>
    );
  }
  if (finalPrice) {
    return <PriceBlock amount={finalPrice} fixedPrice={getDataAttributePrices(fixedPrice, 'price')} />;
  }

  return <PriceBlock amount={price} fixedPrice={getDataAttributePrices(fixedPrice, 'price')} />;
};

export default Price;
