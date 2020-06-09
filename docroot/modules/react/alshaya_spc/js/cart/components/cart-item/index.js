import React from 'react';

import CheckoutConfigurableOption from '../../../utilities/checkout-configurable-option';
import CartPromotion from '../cart-promotion';
import CartItemOOS from '../cart-item-oos';
import CartItemError from '../cart-item-error';
import ItemLowQuantity from '../item-low-quantity';
import CheckoutItemImage from '../../../utilities/checkout-item-image';
import CartQuantitySelect from '../cart-quantity-select';
import { updateCartItemData } from '../../../utilities/update_cart';
import SpecialPrice from '../../../utilities/special-price';
import dispatchCustomEvent from '../../../utilities/events';
import Notifications from './components/Notifications';
import QtyLimit from '../qty-limit';
import DynamicPromotionProductItem
  from '../dynamic-promotion-banner/DynamicPromotionProductItem';
import CartItemFree from '../cart-item-free';
import { getStorageInfo } from '../../../utilities/storage';
import { isQtyLimitReached } from '../../../utilities/checkout_util';
import TrashIconSVG from '../../../svg-component/trash-icon-svg';

export default class CartItem extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: true,
      productInfo: null,
    };
  }

  componentDidMount() {
    const { item } = this.props;
    Drupal.alshayaSpc.getProductData(item.sku, this.productDataCallback);
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

      // If max sale quantity feature enabled.
      if (drupalSettings.quantity_limit_enabled) {
        const { callable } = this.props;
        callable(productData.sku);
      }
    }
  };

  /**
   * Remove item from the cart.
   */
  removeCartItem = (sku, action, id) => {
    // Adding class on remove button for showing progress when click.
    document.getElementById(`remove-item-${id}`).classList.add('loading');
    const afterCartUpdate = () => {
      // Remove loading class.
      document.getElementById(`remove-item-${id}`).classList.remove('loading');
    };
    this.triggerUpdateCart({
      action,
      sku,
      qty: 0,
      callback: afterCartUpdate,
      successMsg: Drupal.t('The product has been removed from your cart.'),
    });
  };

  /**
   * Trigger update cart api call.
   */
  triggerUpdateCart = ({
    action, sku, qty, successMsg, callback = null,
  }) => {
    const cartData = updateCartItemData(action, sku, qty);
    if (cartData instanceof Promise) {
      cartData.then((result) => {
        if (callback !== null) {
          callback();
        }
        const cartResult = result;

        let messageInfo = null;
        if (cartResult.error !== undefined) {
          messageInfo = {
            type: 'error',
            message: cartResult.error_message,
          };
        } else {
          messageInfo = {
            type: 'success',
            message: successMsg,
          };
        }

        let triggerRecommendedRefresh = false;
        const itemsLength = Object.keys(cartResult.items).length;
        if (cartResult.items !== undefined
          && itemsLength > 0) {
          // Trigger if item is removed.
          if (action === 'remove item') {
            triggerRecommendedRefresh = true;
          } else {
            const cartFromStorage = getStorageInfo();
            // If number of items in storage not matches with
            // what we get from mdc, we refresh recommended products.
            if (cartFromStorage !== null
              && cartFromStorage.cart !== undefined
              && cartFromStorage.cart.items !== undefined
              && itemsLength !== Object.keys(cartFromStorage.cart.items).length) {
              triggerRecommendedRefresh = true;
            }
          }
        }

        // Refreshing mini-cart.
        const eventMiniCart = new CustomEvent('refreshMiniCart', { bubbles: true, detail: { data: () => cartResult } });
        document.dispatchEvent(eventMiniCart);

        // Refreshing cart components.
        const eventCart = new CustomEvent('refreshCart', { bubbles: true, detail: { data: () => cartResult } });
        document.dispatchEvent(eventCart);

        // Trigger message.
        if (messageInfo !== null) {
          dispatchCustomEvent('spcCartMessageUpdate', messageInfo);
        }

        // Trigger recommended products refresh.
        if (triggerRecommendedRefresh) {
          dispatchCustomEvent('spcRefreshCartRecommendation', {
            items: cartResult.items,
          });
        }

        // If qty limit enabled.
        if (drupalSettings.quantity_limit_enabled) {
          const { skus, callable } = this.props;
          Object.entries(skus).forEach(([, productSku]) => {
            callable(productSku);
          });
        }
      });
    }
  }

  render() {
    const { wait } = this.state;
    if (wait === true) {
      return (null);
    }

    const {
      item: {
        sku,
        qty,
        id,
        freeItem,
        stock,
        finalPrice,
        in_stock: inStock,
        error_msg: itemErrorMsg,
      },
      qtyLimit: currentQtyLimit,
      animationOffset,
      productPromotion,
    } = this.props;

    const {
      productInfo: {
        image,
        options,
        promotions,
        title,
        url,
        price,
        maxSaleQty,
      },
    } = this.state;
    const cartImage = {
      url: image,
      alt: title,
      title,
    };

    const { isItemError, errorMessage } = this.state;
    let OOSClass = '';
    if (inStock !== true) {
      OOSClass = 'error';
    }

    const animationDelayValue = `${0.3 + animationOffset}s`;

    let dynamicPromoLabels = null;
    if (productPromotion !== false && productPromotion.labels.length !== 0) {
      Object.values(productPromotion.labels).forEach((message) => {
        dynamicPromoLabels = message;
      });
    }

    const qtyLimitClass = (drupalSettings.quantity_limit_enabled
      && currentQtyLimit > maxSaleQty
      && maxSaleQty > 0)
      ? 'sku-max-quantity-limit-reached'
      : '';

    return (
      <div
        className={`spc-cart-item fadeInUp ${qtyLimitClass}`}
        style={{ animationDelay: animationDelayValue }}
        data-sku={sku}
      >
        <div className="spc-product-tile">
          <div className="spc-product-image">
            <CheckoutItemImage img_data={cartImage} />
          </div>
          <div className="spc-product-container">
            <div className="spc-product-title-price">
              <div className="spc-product-title">
                <a href={url}>{title}</a>
              </div>
              <div className="spc-product-price">
                <SpecialPrice
                  price={parseFloat(price)}
                  finalPrice={parseFloat(finalPrice)}
                />
              </div>
            </div>
            <div className="spc-product-attributes-wrapper">
              {options.map((key) => <CheckoutConfigurableOption key={`${sku}-${key.value}`} label={key} />)}
            </div>
          </div>
          <div className="spc-product-tile-actions">
            <button title={Drupal.t('remove this item')} type="button" id={`remove-item-${id}`} className={`spc-remove-btn ${OOSClass}`} onClick={() => { this.removeCartItem(sku, 'remove item', id); }}><TrashIconSVG /></button>
            <div className="qty">
              <div className="qty-loader-placeholder" />
              <CartQuantitySelect
                qty={qty}
                stock={stock}
                sku={sku}
                is_disabled={!inStock || freeItem}
                onQtyChange={this.triggerUpdateCart}
                maxLimit={
                  drupalSettings.quantity_limit_enabled && maxSaleQty !== 0 ? maxSaleQty : null
                }
              />
            </div>
          </div>
        </div>
        <div className="spc-promotions">
          {promotions.map((promo) => <CartPromotion key={`${sku}-${promo.text}`} promo={promo} sku={sku} link />)}
        </div>
        <Notifications>
          <CartItemOOS type="warning" inStock={inStock} />
          <ItemLowQuantity type="alert" stock={stock} qty={qty} in_stock={inStock} />
          {drupalSettings.quantity_limit_enabled
          && (
            <QtyLimit
              type="conditional"
              showWarning={
                parseInt(maxSaleQty, 10) !== 0
                && (parseInt(currentQtyLimit, 10) >= parseInt(maxSaleQty, 10)
                  || (itemErrorMsg !== undefined && isQtyLimitReached(itemErrorMsg) >= 0))
              }
              showAlert={
                parseInt(maxSaleQty, 10) !== 0
                && parseInt(currentQtyLimit, 10) < parseInt(maxSaleQty, 10)
              }
              errMsg={itemErrorMsg}
              filled="true"
              qty={currentQtyLimit}
              maxSaleQty={maxSaleQty}
            />
          )}
          <CartItemFree type="alert" filled="true" freeItem={freeItem} />
          <DynamicPromotionProductItem type="alert" dynamicPromoLabels={dynamicPromoLabels} />
          {isItemError && (<CartItemError type="alert" errorMessage={errorMessage} />)}
        </Notifications>
      </div>
    );
  }
}
