import React from 'react';

import CartPromotion from '../../cart/components/cart-promotion';
import SpecialPrice from '../special-price';

const RecommendedProduct = ({ item, itemKey }) => {
  const itemUrl = `product-quick-view/${item.nid}/nojs`;
  return (
    <>
      <a href={Drupal.url(itemUrl)} className="use-ajax above-mobile-block recommended-product" data-dialog-type="modal" data-sku={itemKey}>
        <div className="spc-product-recommended-image">
          {item.extra_data.cart_image !== undefined
            ? (
              <img
                src={item.extra_data.cart_image.url}
                alt={item.extra_data.cart_image.alt}
                title={item.extra_data.cart_image.title}
              />
            )
            : null}
        </div>
        <div className="product-title">{item.title}</div>
        <div className="spc-product-price">
          <SpecialPrice price={item.original_price} finalPrice={item.final_price} />
        </div>
      </a>
      <div className="spc-promotions">
        {item.promo.map((key) => <CartPromotion key={key} promo={key} link />)}
      </div>
    </>
  );
};

export default RecommendedProduct;
