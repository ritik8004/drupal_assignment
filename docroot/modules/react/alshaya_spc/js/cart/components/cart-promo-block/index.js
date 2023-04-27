import React from 'react';

import { applyRemovePromo } from '../../../utilities/update_cart';
import SectionTitle from '../../../utilities/section-title';
import dispatchCustomEvent from '../../../utilities/events';
import DynamicPromotionCode from './DynamicPromotionCode';
import { openCartFreeGiftModal, getCartFreeGiftModalId } from '../../../utilities/free_gift_util';
import Advantagecard from '../../../utilities/advantagecard';
import { isEgiftCardEnabled } from '../../../../../js/utilities/util';
import { cartItemIsVirtual } from '../../../utilities/egift_util';
import isHelloMemberEnabled from '../../../../../js/utilities/helloMemberHelper';
import HelloMemberCartOffersVouchers from '../../../hello-member-loyalty/components/hello-member-cart-offer-voucher';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

export default class CartPromoBlock extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      promoApplied: false,
      buttonText: Drupal.t('apply'),
      disabled: false,
      productInfo: {},
    };
  }

  componentDidMount() {
    const { coupon_code: couponCode, items } = this.props;

    if (couponCode.length > 0) {
      this.setState({
        promoApplied: true,
        buttonText: Drupal.t('applied'),
        disabled: true,
      });

      document.getElementById('promo-code').value = couponCode;
    }

    document.addEventListener('spcCartPromoError', this.cartPromoEventErrorHandler, false);

    // Get cart items product data.
    Object.keys(items).forEach((key) => {
      // Skip the get product data for virtual product ( This is applicable
      // when egift card module is enabled and cart item is virtual.)
      if (isEgiftCardEnabled() && cartItemIsVirtual(items[key])) {
        return;
      }
      Drupal.alshayaSpc.getProductData(key, this.productDataCallback, {
        parentSKU: items[key].parentSKU,
      });
    });
  }

  componentDidUpdate(prevProps) {
    const { coupon_code: couponCode } = this.props;
    const { coupon_code: prevCouponCode } = prevProps;

    if (couponCode !== prevCouponCode) {
      this.refreshState(couponCode);
    }
  }

  componentWillUnmount() {
    document.removeEventListener('spcCartPromoError', this.cartPromoEventErrorHandler, false);
  }

  /**
   * Call back to get product data from storage.
   */
  productDataCallback = (productData) => {
    const { productInfo } = this.state;
    const data = productInfo;
    // If sku info available.
    if (
      productData.freeGiftPromotion !== null
      && productData.sku !== undefined
      && Object.keys(productData.freeGiftPromotion).length > 0
    ) {
      const freeGiftData = productData.freeGiftPromotion;
      if (freeGiftData['#promo_type'] === 'FREE_GIFT_SUB_TYPE_ONE_SKU') {
        data[freeGiftData['#promo_code']] = {
          code: freeGiftData['#promo_type'],
          skuType: freeGiftData['#free_sku_type'],
          skuCode: freeGiftData['#free_sku_code'],
        };
      } else if (freeGiftData['#promo_code'].length > 0) {
        data[freeGiftData['#promo_code'][0].value] = {
          code: freeGiftData['#promo_type'],
          skuType: freeGiftData['#free_sku_type'],
          skuCode: freeGiftData['#free_sku_code'],
        };
      }
      this.setState({
        productInfo: data,
      });
    }
  };

  refreshState = (couponCode) => {
    if (couponCode.length > 0) {
      this.setState({
        promoApplied: true,
        buttonText: Drupal.t('applied'),
        disabled: true,
      });
    } else {
      this.setState({
        promoApplied: false,
        buttonText: Drupal.t('apply'),
        disabled: false,
      });
    }

    document.getElementById('promo-code').value = couponCode;
  };

  /**
   * Handle error of invalid promo.
   */
  cartPromoEventErrorHandler = (e) => {
    const errorMessage = e.detail.message;
    document.getElementById('promo-message').innerHTML = errorMessage;
    document.getElementById('promo-message').classList.add('error');
    document.getElementById('promo-code').classList.add('error');
    // Trigger cart update to remove any message on cart.
    dispatchCustomEvent('spcCartMessageUpdate', {});
    // Push error message to GTM.
    Drupal.logJavascriptError('promo-code', errorMessage, GTM_CONSTANTS.CART_ERRORS);
  }

  promoAction = (promoApplied, inStock, promoCoupons = null) => {
    // If not in stock.
    if (inStock === false) {
      return;
    }
    let promoValue = document.getElementById('promo-code').value.trim();

    // If empty promo text.
    if (promoApplied === false && promoValue.length === 0) {
      document.getElementById('promo-message').innerHTML = Drupal.t('please enter promo code.');
      document.getElementById('promo-message').classList.add('error');
      document.getElementById('promo-code').classList.add('error');
      return;
    }
    // If Advantage card enabled and not valid.
    if (Advantagecard.isAdvantagecardEnabled()
      && Advantagecard.isValidAdvantagecard(promoValue) === false) {
      document.getElementById('promo-message').innerHTML = Drupal.t('Please enter valid Advantage card code.');
      document.getElementById('promo-message').classList.add('error');
      document.getElementById('promo-code').classList.add('error');
      return;
    }

    const action = (promoApplied === true) ? 'remove coupon' : 'apply coupon';

    // Adding class on promo button for showing progress when click and applying promo.
    if (promoApplied !== true) {
      document.getElementById('promo-action-button').classList.add('loading');
    } else {
      document.getElementById('promo-remove-button').classList.add('loading');
    }

    const cartData = applyRemovePromo(action, promoValue);
    if (cartData instanceof Promise) {
      cartData.then((result) => {
        let freeGiftPromo = '';
        if (promoCoupons !== null) {
          freeGiftPromo = promoCoupons[promoValue];
        }
        if (freeGiftPromo !== undefined && action === 'apply coupon' && freeGiftPromo.code === 'FREE_GIFT_SUB_TYPE_ONE_SKU') {
          const body = document.querySelector('body');
          body.classList.add('free-gifts-modal-overlay');
          // Render free gift modal on cart page.
          window.commerceBackend.startFreeGiftModalProcess(
            document.getElementById(getCartFreeGiftModalId(freeGiftPromo.skuCode)).dataset.sku.split(','),
            false,
            document.getElementsByClassName('coupon-code')[0].textContent,
          );
          document.getElementById('promo-action-button').classList.remove('loading');
        } else if (freeGiftPromo !== undefined && action === 'apply coupon' && freeGiftPromo.code === 'FREE_GIFT_SUB_TYPE_ALL_SKUS' && freeGiftPromo.skuType === 'configurable') {
          openCartFreeGiftModal(freeGiftPromo.skuCode);
          document.getElementById('promo-action-button').classList.remove('loading');
        } else {
          // Removing button clicked class.
          document.getElementById('promo-action-button').classList.remove('loading');
          document.getElementById('promo-remove-button').classList.remove('loading');
          if (Advantagecard.isAdvantagecardEnabled()
            && (Advantagecard.isAdvantageCardApplied(result.totals.items)
            || (promoValue.includes(drupalSettings.alshaya_spc.advantageCard.advantageCardPrefix)
              && result.response_message.status === 'error_coupon'))) {
            // For Advantage card set promoValue to Advantage_Card_uid.
            promoValue = `Advantage_Card_${drupalSettings.userDetails.userID}`;
          }
          // If coupon is not valid.
          if (Advantagecard.isAllItemsExcludedForAdvCard(result.totals)) {
            let messageInfo = null;
            messageInfo = {
              type: 'error',
              message: result.response_message.msg,
            };
            dispatchCustomEvent('spcCartMessageUpdate', messageInfo);
            const event = new CustomEvent('promoCodeFailed', { bubbles: true, detail: { data: promoValue } });
            document.dispatchEvent(event);
            // Push error message to GTM.
            Drupal.logJavascriptError('promo-code', messageInfo.message, GTM_CONSTANTS.CART_ERRORS);
          }
          if (result.response_message.status === 'error_coupon'
            && !Advantagecard.isAllItemsExcludedForAdvCard(result.totals)) {
            const event = new CustomEvent('promoCodeFailed', { bubbles: true, detail: { data: promoValue } });
            document.getElementById('promo-message').innerHTML = result.response_message.msg;
            document.getElementById('promo-message').classList.add('error');
            document.getElementById('promo-code').classList.add('error');
            // Dispatch event promoCodeFailed for GTM.
            document.dispatchEvent(event);
            // Push error message to GTM.
            Drupal.logJavascriptError('promo-code', result.response_message.msg, GTM_CONSTANTS.CART_ERRORS);
          } else if (result.response_message.status === 'success') {
            document.getElementById('promo-message').innerHTML = '';
            // Initially promoApplied was false, means promo is applied now.
            if (promoApplied === false) {
              this.setState({
                promoApplied: true,
                buttonText: Drupal.t('applied'),
                disabled: true,
              });
              // Dispatch event promoCodeSuccess for GTM.
              const event = new CustomEvent('promoCodeSuccess', { bubbles: true, detail: { data: promoValue } });
              document.dispatchEvent(event);
            } else {
              // It means promo is removed.
              this.setState({
                promoApplied: false,
                buttonText: Drupal.t('apply'),
                disabled: false,
              });

              document.getElementById('promo-code').value = '';
            }

            // Refreshing mini-cart.
            const miniCartEvent = new CustomEvent('refreshMiniCart', { bubbles: true, detail: { data: () => result } });
            document.dispatchEvent(miniCartEvent);

            // Refreshing cart components..
            const refreshCartEvent = new CustomEvent('refreshCart', { bubbles: true, detail: { data: () => result } });
            document.dispatchEvent(refreshCartEvent);
          }

          // Trigger cart update to remove any message on cart.
          if (!Advantagecard.isAllItemsExcludedForAdvCard(result.totals)) {
            dispatchCustomEvent('spcCartMessageUpdate', {});
          }
        }
      });
    }
  };

  render() {
    const {
      productInfo,
      promoApplied,
      disabled,
      buttonText,
    } = this.state;

    const {
      inStock, dynamicPromoLabelsCart, totals, hasExclusiveCoupon,
    } = this.props;
    const promoRemoveActive = promoApplied ? 'active' : '';
    let disabledState = false;
    // Disable the promo field if out of stock or disabled.
    if (disabled === true || inStock === false) {
      disabledState = true;
    }
    // Check for Dynamic promotion codes.
    let couponCode = null;
    let couponLabel = null;
    if (dynamicPromoLabelsCart !== null) {
      if (dynamicPromoLabelsCart.next_eligible !== undefined
        && dynamicPromoLabelsCart.next_eligible.type !== undefined) {
        const {
          coupon,
          couponDiscount,
          threshold_reached: thresholdReached,
        } = dynamicPromoLabelsCart.next_eligible;

        if (thresholdReached === true
          && hasValue(coupon)
          && hasValue(couponDiscount)) {
          couponCode = coupon;
          couponLabel = Drupal.t('Use and get @percent% off', { '@percent': couponDiscount });
        }
      }
    }

    // Remove any promo coupons errors on promo
    // coupon application success.
    if (promoApplied) {
      const promoError = document.querySelector('#promo-message.error');
      if (promoError !== null) {
        promoError.outerHTML = '<div id="promo-message" />';
      }
    }

    return (
      <div className="spc-promo-code-block fadeInUp" style={{ animationDelay: '0.4s' }}>
        <SectionTitle>{Drupal.t('have a promo code?')}</SectionTitle>
        {isHelloMemberEnabled() && (
          <HelloMemberCartOffersVouchers
            totals={totals}
          />
        )}
        <div className="block-content">
          <input
            id="promo-code"
            disabled={disabledState}
            type="text"
            placeholder={Drupal.t('Promo code')}
          />
          <button id="promo-remove-button" type="button" className={`promo-remove ${promoRemoveActive}`} onClick={() => { this.promoAction(promoApplied, inStock); }}>{Drupal.t('Remove')}</button>
          <button id="promo-action-button" type="button" disabled={disabledState} className="promo-submit" onClick={() => { this.promoAction(promoApplied, inStock, productInfo); }}>{buttonText}</button>
          <div id="promo-message" />
          {/* Displaying success message below the promo text field only when exclusive
           coupon gets applied in basket.
           This message will be shown all the time even after page load,
           till exclusive coupon/promo is applied on basket. */}
          {hasExclusiveCoupon === true
            && <div id="exclusive-promo-message">{Drupal.t('Promotion code is applied on the original price. All other promotions were removed.')}</div>}
          {/* Displaying dynamic promotion code only when no exclusive
           coupon gets applied in basket. */}
          {hasExclusiveCoupon !== true
            && (<DynamicPromotionCode code={couponCode} label={couponLabel} />)}
        </div>
      </div>
    );
  }
}
