import React from 'react';
import SpecialPrice from '../../../../alshaya_spc/js/utilities/special-price';

const WishlistProduct = ({ item, itemKey }) => {
  const itemUrl = `product-quick-view/${item.nid}/nojs`;
  return (
    <div className="wishlist-product-wrapper">
      <div className="wishlist-product-content">
        <a href={Drupal.url(itemUrl)} className="wishlist-product" data-sku={itemKey}>
          <div className="wislist-product-image">
            <img
              src={item.url}
              alt={item.alt}
              title={item.title}
            />
          </div>
          <div className="product-title">{item.title}</div>
          <div className="wishlist-product-price">
            <SpecialPrice price={item.original_price} finalPrice={item.final_price} />
          </div>
        </a>
      </div>
      <div className="wishlist-actions">
        <span className="remove-wishlist">{Drupal.t('Remove')}</span>
        <span className="share-product">{Drupal.t('Share')}</span>
      </div>
    </div>
  );
};

export default WishlistProduct;
