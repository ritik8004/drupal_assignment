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
import { isQtyLimitReached, customStockErrorMessage } from '../../../utilities/checkout_util';
import validateCartResponse from '../../../utilities/validation_util';
import TrashIconSVG from '../../../svg-component/trash-icon-svg';
import CartPromotionFreeGift from '../cart-promotion-freegift';
import ConditionalView from '../../../common/components/conditional-view';
import AdvantageCardExcludedItem from '../advantage-card';
import CartShippingMethods from '../cart-shipping-methods';
import { isWishlistEnabled, isProductExistInWishList } from '../../../../../js/utilities/wishlistHelper';
import WishlistContainer from '../../../../../js/utilities/components/wishlist-container';
import WishlistPopupBlock from '../../../wishlist/components/popup-block';
import { getDeliveryAreaStorage } from '../../../utilities/delivery_area_util';
import { isExpressDeliveryEnabled } from '../../../../../js/utilities/expressDeliveryHelper';
import { isEgiftCardEnabled } from '../../../../../js/utilities/util';
import { cartItemIsVirtual } from '../../../utilities/egift_util';

export default class CartItem extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: true,
      productInfo: null,
      showWishlistPopup: false,
      // wishlistResponse tracks if user has responded in wishlist popup.
      wishlistResponse: false,
    };
  }

  componentDidMount() {
    const { item } = this.props;
    // Skip the get product data for virtual product ( This is applicable
    // when egift card module is enabled and cart item is virtual.)
    if (!(isEgiftCardEnabled() && cartItemIsVirtual(item))) {
      Drupal.alshayaSpc.getProductData(item.sku, this.productDataCallback, item.parentSKU);
    }

    if (isWishlistEnabled()) {
      // Add event listener for add to wishlist action.
      document.addEventListener('productAddedToWishlist', this.handleAddToWishList, false);
    }
  }

  componentDidUpdate() {
    Drupal.ajax.bindAjaxLinks(document.getElementById('spc-cart'));
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
   * To open the wishlist confirmation popup.
   * Popup will show up while deleting item from cart.
   */
  openWishlistModal = () => {
    this.setState({
      showWishlistPopup: true,
    });
  }

  /**
   * To close the wishlist confirmation popup.
   * Once popup closes, we move on to deleting the item from cart.
   */
  closeWishlistModal = (removeFromBasket = true) => {
    const { item: { sku, id } } = this.props;
    if (!removeFromBasket) {
      this.setState({
        showWishlistPopup: false,
      });
    } else {
      this.setState({ showWishlistPopup: false, wishlistResponse: removeFromBasket }, () => {
        this.removeCartItem(sku, 'remove item', id);
      });
    }
  };

  /**
   * Once item is added to wishlist, remove item from cart.
   */
  handleAddToWishList = (e) => {
    if (typeof e.detail.productInfo !== 'undefined'
      && e.detail.productInfo.variant
      && (typeof e.detail.removeFromCart === 'undefined'
      || e.detail.removeFromCart)) {
      const { item: { sku, id } } = this.props;
      // Compare with sku to remove the right item from cart.
      if (e.detail.productInfo.variant === sku) {
        this.setState({ wishlistResponse: true }, () => {
          this.removeCartItem(sku, 'remove item', id);
        });
      }
    }
  };

  /**
   * Remove item from the cart.
   */
  removeCartItem = (sku, action, id) => {
    // Open wishlist confirmation popup if feature is enabled.
    if (isWishlistEnabled()) {
      const { wishlistResponse } = this.state;
      const { productInfo: { parentSKU } } = this.state;
      const wishlistSku = parentSKU || sku;
      if (!wishlistResponse && !(isProductExistInWishList(wishlistSku))) {
        this.openWishlistModal();
        return;
      }
    }
    const elem = document.getElementById(`remove-item-${id}`);
    if (typeof elem !== 'undefined' && elem !== null) {
      // Adding class on remove button for showing progress when click.
      elem.classList.add('loading');
      const afterCartUpdate = () => {
        // Remove loading class.
        elem.classList.remove('loading');
      };

      this.triggerUpdateCart({
        action,
        sku,
        qty: 0,
        callback: afterCartUpdate,
        successMsg: Drupal.t('The product has been removed from your cart.'),
      });
    }
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
        if (!validateCartResponse(result)) {
          return null;
        }

        if (callback !== null) {
          callback();
        }
        const cartResult = result;

        let messageInfo = null;
        if (cartResult.error !== undefined) {
          const errorMessage = customStockErrorMessage(cartResult);
          messageInfo = {
            type: 'error',
            message: errorMessage,
          };
        } else {
          messageInfo = {
            type: 'success',
            message: successMsg,
          };
        }

        let triggerRecommendedRefresh = false;
        const itemsLength = (cartResult.items !== undefined)
          ? Object.keys(cartResult.items).length
          : 0;
        if (cartResult.items !== undefined
          && itemsLength > 0) {
          // Trigger if item is removed.
          if (action === 'remove item') {
            triggerRecommendedRefresh = true;
          } else {
            const cartFromStorage = Drupal.getItemFromLocalStorage('cart_data');
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

        // Update cart shipping methods on cart update when item
        // is removed from the cart and cart still contains items.
        if (isExpressDeliveryEnabled() && action === 'remove item' && itemsLength > 0) {
          const currentArea = getDeliveryAreaStorage();
          dispatchCustomEvent('displayShippingMethods', currentArea);
        }

        // Trigger message.
        if (messageInfo !== null) {
          dispatchCustomEvent('spcCartMessageUpdate', messageInfo);
        }

        // Trigger recommended products refresh.
        if (triggerRecommendedRefresh) {
          dispatchCustomEvent('spcRefreshCartRecommendation', {
            items: cartResult.items,
            triggerRecommendedRefresh,
          });
        }

        // If qty limit enabled.
        if (drupalSettings.quantity_limit_enabled) {
          const { skus, callable } = this.props;
          Object.entries(skus).forEach(([, productSku]) => {
            callable(productSku);
          });
        }

        return null;
      });
    }
  }

  render() {
    const { wait, showWishlistPopup } = this.state;
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
      couponCode,
      selectFreeGift,
      totalsItems,
      cartShippingMethods,
    } = this.props;

    const {
      productInfo: {
        id: skuId,
        image,
        options,
        promotions,
        freeGiftPromotion,
        title,
        url,
        price,
        maxSaleQty,
        parentSKU,
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

    const itemCodeLabel = drupalSettings.item_code_label
      ? <div>{`${drupalSettings.item_code_label}: ${sku}`}</div>
      : null;

    const qtyLimitClass = (drupalSettings.quantity_limit_enabled
      && currentQtyLimit > maxSaleQty
      && maxSaleQty > 0)
      ? 'sku-max-quantity-limit-reached'
      : '';

    const attributeOptions = [];
    // Add configurable options only for configurable product.
    if (isWishlistEnabled() && parentSKU) {
      Object.keys(options).forEach((key) => {
        const option = {
          option_id: options[key].option_id,
          option_value: options[key].option_value,
        };
        attributeOptions.push(option);
      });
    }

    return (
      <div
        className={`spc-cart-item fadeInUp ${qtyLimitClass}`}
        style={{ animationDelay: animationDelayValue }}
        data-sku={sku}
        gtm-old-price={price}
      >
        <div className="spc-product-tile">
          <div className="spc-product-image">
            <CheckoutItemImage img_data={cartImage} />
          </div>
          <div className="spc-product-container">
            <div className="spc-product-title-price">
              <div className="spc-product-title">
                {freeItem
                  ? <a href={Drupal.url(`free-gift/${skuId}/nojs`)} className="use-ajax" data-dialog-type="modal">{title}</a>
                  : <a href={url}>{title}</a>}
              </div>
              <div className="spc-product-price">
                <SpecialPrice price={price} freeItem={freeItem} finalPrice={finalPrice} />
              </div>
            </div>
            <div className="spc-product-attributes-wrapper">
              { itemCodeLabel }
              {options.map((key) => <CheckoutConfigurableOption key={`${sku}-${key.value}`} label={key} />)}
            </div>
            <ConditionalView condition={isWishlistEnabled() && !freeItem}>
              <div className="spc-product-wishlist-link">
                <WishlistContainer
                  context="cart"
                  position="cart-item"
                  skuCode={parentSKU || sku}
                  sku={sku}
                  title={title}
                  options={attributeOptions}
                  format="text"
                />
              </div>
              {/* When user clicks on delete, pop shown asking if product added to wishlist. */}
              <ConditionalView condition={showWishlistPopup}>
                <WishlistPopupBlock
                  sku={parentSKU || sku}
                  variant={sku}
                  title={title}
                  itemImage={cartImage}
                  options={attributeOptions}
                  closeWishlistModal={this.closeWishlistModal}
                />
              </ConditionalView>
            </ConditionalView>
          </div>
          <div className="spc-product-tile-actions">
            <button
              title={Drupal.t('remove this item')}
              type="button"
              id={`remove-item-${id}`}
              className={`spc-remove-btn ${OOSClass}`}
              disabled={(couponCode.length === 0 && freeItem)}
              onClick={() => { this.removeCartItem(sku, 'remove item', id); }}
            >
              <TrashIconSVG />
            </button>
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
        <div className="spc-cart-item-bottom-wrapper">
          <div className="spc-promotions free-gift-container">
            {promotions.map((promo) => <CartPromotion key={`${sku}-${promo.text}`} promo={promo} sku={sku} couponCode={couponCode} link />)}
            <ConditionalView condition={freeGiftPromotion !== null}>
              <CartPromotionFreeGift
                key={`${sku}-free-gift`}
                promo={freeGiftPromotion}
                sku={sku}
                couponCode={couponCode}
                selectFreeGift={selectFreeGift}
              />
            </ConditionalView>
          </div>
          {/* Displaying cart shipping methods when express delivery enabled */}
          <div className="spc-cart-shipping-methods shipping-methods">
            <ConditionalView condition={cartShippingMethods !== null}>
              <CartShippingMethods
                sku={sku}
                parentSKU={parentSKU}
                cartShippingMethods={cartShippingMethods}
              />
            </ConditionalView>
          </div>
        </div>
        <Notifications>
          <CartItemOOS type="warning" inStock={inStock} />
          <AdvantageCardExcludedItem type="warning" totalsItems={totalsItems} id={id} />
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
                !drupalSettings.hide_max_qty_limit_message
                && parseInt(maxSaleQty, 10) !== 0
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
