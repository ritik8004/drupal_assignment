import React from 'react';
import PriceElement from './price-element';
import { calculateDiscount } from '../../price';
import ConditionalView from '../conditional-view';
import PriceBlock from './price-block';

const Price = ({ price, finalPrice }) => {
  const initalPrice = parseFloat(price.replace(',', ''));
  const endPrice = parseFloat(finalPrice.replace(',', ''));
  const hasDiscount = initalPrice > 0 && endPrice > 0 && endPrice < initalPrice;
  const discount = hasDiscount ? calculateDiscount(initalPrice, endPrice) : 0;

  return (
    <>
      <ConditionalView condition={hasDiscount}>
        <PriceBlock>
          <div className="has--special--price">
            <PriceElement amount={initalPrice} />
          </div>
          <div className="special--price">
            <PriceElement amount={endPrice} />
          </div>
          <ConditionalView condition={discount > 0}>
            <div className="price--discount">
              (
              {Drupal.t('Save @discount%', { '@discount': discount })}
              )
            </div>
          </ConditionalView>
        </PriceBlock>
      </ConditionalView>

      <ConditionalView condition={!hasDiscount && endPrice}>
        <PriceBlock amount={endPrice} />
      </ConditionalView>

      <ConditionalView condition={!hasDiscount && !endPrice}>
        <PriceBlock amount={initalPrice} />
      </ConditionalView>
    </>
  );
};

export default Price;
