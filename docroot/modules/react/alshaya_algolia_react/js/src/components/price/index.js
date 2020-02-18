import React from 'react';
import PriceElement from './PriceElement';
import { calculateDiscount } from '../../utils';

const PriceBlock = props => {
  return (
    <div className="price-block">
      {
        (typeof props.children != 'undefined' && props.children.length > 0)
          ? props.children
          : <PriceElement {...props} />
      }
    </div>
  );
}

const Price = ({ price, final_price }) => {
  if (price > 0 && final_price > 0 && final_price < price) {
    const discount = calculateDiscount(price, final_price);
    const discountTxt = (discount > 0)
      ? (<div className="price--discount">({Drupal.t('Save @discount%', { '@discount': discount })})</div>)
      : '';

    return (
      <PriceBlock>
        <div className="has--special--price">
          <PriceElement amount={price} />
        </div>
        <div className="special--price">
          <PriceElement amount={final_price} />
        </div>
        {discountTxt}
      </PriceBlock>
    );
  }
  else if (final_price) {
    return <PriceBlock amount={final_price} />;
  }

  return <PriceBlock amount={price} />;
}

export default Price;
