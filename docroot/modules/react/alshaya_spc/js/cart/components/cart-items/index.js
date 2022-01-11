import React from 'react';

import CartItem from '../cart-item';
import CartVirtualItem from '../../../egift-card/components/egift-virtual-cart-item';

export default class CartItems extends React.Component {
  static products = {};

  static qtyLimits = {};

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
        const product = Drupal.getItemFromLocalStorage(key);
        if (product !== null && product.maxSaleQtyParent) {
          qtyLimits[product.parentSKU] = (typeof qtyLimits[product.parentSKU] !== 'undefined')
            ? qtyLimits[product.parentSKU] + productData.qty
            : productData.qty;
        }
      });

      // If more than one child sku of a common parent sku is added to cart
      // then the total qty of all chid sku in cart, should not exceed the
      // qty limit set for parent sku,therefore we are calculating the
      // total qty of added products in cart by parent sku.
      if (Object.keys(qtyLimits).length > 0) {
        Object.entries(items).forEach(([, productData]) => {
          const key = `product:${drupalSettings.path.currentLanguage}:${productData.sku}`;
          const product = Drupal.getItemFromLocalStorage(key);
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
    const {
      items,
      dynamicPromoLabelsProduct,
      couponCode,
      totals,
      cartShippingMethods,
    } = this.props;
    const productItems = [];

    const skus = {};
    Object.entries(items).forEach(([key, product], index) => {
      let productPromotion = false;
      skus[product.sku] = product.sku;
      if (dynamicPromoLabelsProduct !== null && key in dynamicPromoLabelsProduct) {
        productPromotion = dynamicPromoLabelsProduct[key];
      }
      const animationOffset = (1 + index) / 5;

      const productQtyLimit = (drupalSettings.quantity_limit_enabled)
        ? (CartItems.qtyLimits[product.sku] || product.qty)
        : 0;

      // Render CartVirtualItem component for virtual Product i.e Egift card
      if (product.isEgiftCard) {
        productItems.push(
          <CartVirtualItem
            animationOffset={animationOffset}
            key={key}
            item={product}
            skus={skus}
            productPromotion={productPromotion}
            couponCode={couponCode}
            totalsItems={totals.items}
          />,
        );
      } else {
        // Render CartItem component for Normal Products
        productItems.push(
          <CartItem
            animationOffset={animationOffset}
            key={key}
            item={product}
            skus={skus}
            callable={this.productCheckCallable}
            qtyLimit={productQtyLimit}
            productPromotion={productPromotion}
            couponCode={couponCode}
            totalsItems={totals.items}
            cartShippingMethods={cartShippingMethods}
          />,
        );
      }
    });

    return (
      <div className="spc-cart-items">{productItems}</div>
    );
  }
}
