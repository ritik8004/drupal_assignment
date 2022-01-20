import React from 'react';

import CheckoutItemImage from '../../../utilities/checkout-item-image';
import CheckoutConfigurableOption from '../../../utilities/checkout-configurable-option';
import SpecialPrice from '../../../utilities/special-price';
import CartPromotion from '../../../cart/components/cart-promotion';
import ProductFlag from '../../../utilities/product-flag';
import CartItemFree from '../../../cart/components/cart-item-free';
import Notifications from '../../../cart/components/cart-item/components/Notifications';
import { isEgiftCardEnabled } from '../../../../../js/utilities/util';
import { cartItemIsVirtual } from '../../../utilities/egift_util';

class CheckoutCartItem extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: true,
      productInfo: null,
    };
  }

  componentDidMount() {
    const { item } = this.props;

    if (Object.prototype.hasOwnProperty.call(item, 'prepared')) {
      this.setState({
        wait: false,
        productInfo: item,
      });

      return;
    }

    // Skip the get product data for virtual product ( This is applicable
    // when egift card module is enabled and cart item is virtual.)
    if (isEgiftCardEnabled() && cartItemIsVirtual(item)) {
      return;
    }

    const parentSKU = item.product_type === 'configurable' ? item.parentSKU : null;
    // Key will be like 'product:en:testsku'
    Drupal.alshayaSpc.getProductData(item.sku, this.productDataCallback, { parentSKU });
  }

  /**
   * Call back to get product data from storage.
   */
  productDataCallback = (productData) => {
    // If sku info available.
    if (productData !== null && productData.sku !== undefined) {
      this.setState({
        wait: false,
        productInfo: productData,
      });
    }
  };

  render() {
    const { wait } = this.state;
    if (wait === true) {
      return (null);
    }

    const {
      item: {
        id,
        finalPrice,
        freeItem,
      },
      context,
      couponCode,
    } = this.props;

    const {
      productInfo: {
        image,
        options: configurableValues,
        title,
        url: relativeLink,
        price: originalPrice,
        promotions,
        sku,
        isNonRefundable,
      },
    } = this.state;

    const cartImage = {
      url: image,
      alt: title,
      title,
    };

    const freeGift = freeItem === true ? 'free-gift' : '';

    return (
      <div className={`product-item ${freeGift}`}>
        <div className="spc-product-image">
          <CheckoutItemImage img_data={cartImage} />
        </div>
        <div className="spc-product-meta-data">
          <div className="spc-product-title-price">
            <div className="spc-product-title">
              {(relativeLink && relativeLink.length > 0)
                ? (<a href={relativeLink}>{title}</a>)
                : title}
            </div>
            <div className="spc-product-price">
              <SpecialPrice price={originalPrice} freeItem={freeItem} finalPrice={finalPrice} />
            </div>
          </div>
          <div className="spc-product-attributes">
            { configurableValues.map((key) => <CheckoutConfigurableOption key={`${key.label}-${id}`} label={key} />) }
          </div>
        </div>
        {context !== 'cart' ? (
          <Notifications>
            <CartItemFree type="alert" filled="true" freeItem={freeItem} />
          </Notifications>
        ) : null}
        <ProductFlag
          flag={isNonRefundable}
          flagText={drupalSettings.alshaya_spc.non_refundable_text}
          tooltipContent={drupalSettings.alshaya_spc.non_refundable_tooltip}
          tooltip
        />
        {context !== 'confirmation' && context !== 'print' && (
          <div className="spc-promotions">
            {promotions.map((key) => <CartPromotion key={`${key}-${sku}`} couponCode={couponCode} promo={key} sku={sku} link />)}
          </div>
        )}
      </div>
    );
  }
}

export default CheckoutCartItem;
