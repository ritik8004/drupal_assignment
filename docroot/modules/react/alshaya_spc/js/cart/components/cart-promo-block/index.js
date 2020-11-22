import React from 'react';

import { applyRemovePromo } from '../../../utilities/update_cart';
import SectionTitle from '../../../utilities/section-title';
import dispatchCustomEvent from '../../../utilities/events';
import DynamicPromotionCode from './DynamicPromotionCode';

export default class CartPromoBlock extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      promoApplied: false,
      buttonText: Drupal.t('apply'),
      disabled: false,
      productInfo: null,
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
      Drupal.alshayaSpc.getProductData(key, this.productDataCallback);
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
    // If sku info available.
    if (productData !== null && productData.sku !== undefined) {
      this.setState({
        productInfo: productData,
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
  }

  promoAction = (promoApplied, inStock, promoCoupons = null) => {
    // If not in stock.
    if (inStock === false) {
      return;
    }
    const promoValue = document.getElementById('promo-code').value.trim();

    // If empty promo text.
    if (promoApplied === false && promoValue.length === 0) {
      document.getElementById('promo-message').innerHTML = Drupal.t('please enter promo code.');
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
        let freeGiftPromoType = '';
        if (promoCoupons !== null) {
          freeGiftPromoType = promoCoupons[promoValue];
        }
        if (action === 'apply coupon' && freeGiftPromoType === 'FREE_GIFT_SUB_TYPE_ONE_SKU') {
          const body = document.querySelector('body');
          body.classList.add('free-gifts-modal-overlay');
          document.getElementById('spc-free-gift').click();
        } else {
          // Removing button clicked class.
          document.getElementById('promo-action-button').classList.remove('loading');
          document.getElementById('promo-remove-button').classList.remove('loading');
          // If coupon is not valid.
          if (result.response_message.status === 'error_coupon') {
            const event = new CustomEvent('promoCodeFailed', { bubbles: true, detail: { data: promoValue } });
            document.getElementById('promo-message').innerHTML = result.response_message.msg;
            document.getElementById('promo-message').classList.add('error');
            document.getElementById('promo-code').classList.add('error');
            // Dispatch event promoCodeFailed for GTM.
            document.dispatchEvent(event);
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
          dispatchCustomEvent('spcCartMessageUpdate', {});
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
    let freeGiftPromotion = null;
    const promoCoupons = {};
    if (productInfo !== null) {
      freeGiftPromotion = productInfo.freeGiftPromotion;
    }
    const { inStock, dynamicPromoLabelsCart } = this.props;
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
          && coupon !== undefined
          && couponDiscount !== undefined) {
          couponCode = coupon;
          couponLabel = Drupal.t('Use and get @percent% off', { '@percent': couponDiscount });
        }
      }
    }

    // Prepare free gift promp type array.
    if (freeGiftPromotion !== null) {
      const freeGifCoupon = freeGiftPromotion['#promo_code'];
      promoCoupons[freeGifCoupon] = freeGiftPromotion['#promo_type'];
    }

    return (
      <div className="spc-promo-code-block fadeInUp" style={{ animationDelay: '0.4s' }}>
        <SectionTitle>{Drupal.t('have a promo code?')}</SectionTitle>
        <div className="block-content">
          <input id="promo-code" disabled={disabledState} type="text" placeholder={Drupal.t('Promo code')} />
          <button id="promo-remove-button" type="button" className={`promo-remove ${promoRemoveActive}`} onClick={() => { this.promoAction(promoApplied, inStock); }}>{Drupal.t('Remove')}</button>
          <button id="promo-action-button" type="button" disabled={disabledState} className="promo-submit" onClick={() => { this.promoAction(promoApplied, inStock, promoCoupons); }}>{buttonText}</button>
          <div id="promo-message" />
          <DynamicPromotionCode code={couponCode} label={couponLabel} />
        </div>
      </div>
    );
  }
}
