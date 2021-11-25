import React from 'react';
import WishlistButton from '../wishlist-button';

const WishlistPdpContent = ({ variantSelected, skuParent, title }) => {
  let productInfo = {};
  if (variantSelected) {
    productInfo = {
      sku: skuParent,
      title,
    };
  } else {
    const attr = document.getElementsByClassName('sku-base-form');
    const sku = attr[0].getAttribute('data-sku');
    if (sku === undefined) {
      return null;
    }
    productInfo = {
      sku,
      title: drupalSettings.productInfo[sku].cart_title,
    };
  }
  return (
    <div className="wishlist-pdp-container">
      <WishlistButton
        productInfo={productInfo}
        context="pdp"
        position="top-right"
        format="icon"
      />
    </div>
  );
};

export default WishlistPdpContent;
