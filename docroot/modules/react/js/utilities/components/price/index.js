import React from 'react';
import PriceElement from './price-element';
import { calculateDiscount, getDataAttributePrices } from '../../price';
import ConditionalView from '../conditional-view';
import PriceBlock from './price-block';

const Price = ({
  price,
  finalPrice,
  fixedPrice = '',
  sku = '',
}) => {
  const initalPrice = parseFloat(price.replace(',', ''));
  const endPrice = parseFloat(finalPrice.replace(',', ''));
  const hasDiscount = initalPrice > 0 && endPrice > 0 && endPrice < initalPrice;
  const discount = hasDiscount ? calculateDiscount(initalPrice, endPrice) : 0;

  return (
    <>
      <ConditionalView condition={hasDiscount}>
        <PriceBlock sku={sku}>
          <div className="has--special--price">
            <PriceElement amount={initalPrice} fixedPrice={getDataAttributePrices(fixedPrice, 'price')} />
          </div>
          <div className="special--price">
            <PriceElement amount={endPrice} fixedPrice={getDataAttributePrices(fixedPrice, 'special_price')} />
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
        <PriceBlock sku={sku} amount={endPrice} fixedPrice={getDataAttributePrices(fixedPrice, 'price')} />
      </ConditionalView>

      <ConditionalView condition={!hasDiscount && !endPrice}>
        <PriceBlock sku={sku} amount={initalPrice} fixedPrice={getDataAttributePrices(fixedPrice, 'price')} />
      </ConditionalView>
    </>
  );
};

export default Price;
