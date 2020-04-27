import React from 'react';

import CartItem from '../cart-item';

export default class CartItems extends React.Component {
  static products = {};

  static qtyLimits = {};

  constructor(props) {
    super(props);
  }

  productCheckCallable = (sku) => {
    const { items } = this.props;
    if (CartItems.products[sku] === undefined) {
      CartItems.products[sku] = sku;
    }

    // If we have all items.
    if (Object.keys(CartItems.products).length === Object.keys(items).length) {
      CartItems.products = {};
      // If all products available, calculate max sale quantity.
      this.calCulateMaxSaleQuantity();
    }
  };

  calCulateMaxSaleQuantity = () => {
    if (drupalSettings.quantity_limit_enabled) {
      const { items } = this.props;
      // Prepare max sale quantity for each item.
      const qtyLimits = {};
      Object.entries(items).forEach(([, productData]) => {
        const key = `product:${drupalSettings.path.currentLanguage}:${productData.sku}`;
        const product = JSON.parse(localStorage.getItem(key));
        qtyLimits[product.parentSKU] = (product !== null
          && typeof qtyLimits[product.parentSKU] !== 'undefined')
          ? qtyLimits[product.parentSKU] + productData.qty
          : productData.qty;
      });

      // If more than one child sku of a common parent sku is added to cart
      // then the total qty of all chid sku in cart, should not exceed the
      // qty limit set for parent sku,therefore we are calculating the
      // total qty of added products in cart by parent sku.
      if (Object.keys(qtyLimits).length > 0) {
        Object.entries(items).forEach(([, productData]) => {
          const key = `product:${drupalSettings.path.currentLanguage}:${productData.sku}`;
          const product = JSON.parse(localStorage.getItem(key));
          if (product !== null) {
            CartItems.qtyLimits[productData.sku] = qtyLimits[product.parentSKU];
          }
        });

        // Re-fresh components.
        this.forceUpdate();
      }
    }
  };

  render() {
    const { items, dynamicPromoLabelsProduct } = this.props;
    const productItems = [];

    const skus = {};
    Object.entries(items).forEach(([key, product], index) => {
      let productPromotion = false;
      skus[product.sku] = product.sku;
      if (dynamicPromoLabelsProduct !== null && key in dynamicPromoLabelsProduct) {
        productPromotion = dynamicPromoLabelsProduct[key];
      }
      const animationOffset = (1 + index) / 5;
      productItems.push(
        <CartItem
          animationOffset={animationOffset}
          key={key}
          item={product}
          skus={skus}
          callable={this.productCheckCallable}
          qtyLimit={CartItems.qtyLimits[product.sku] || 0}
          productPromotion={productPromotion}
        />,
      );
    });

    return (
      <div className="spc-cart-items">{productItems}</div>
    );
  }
}
