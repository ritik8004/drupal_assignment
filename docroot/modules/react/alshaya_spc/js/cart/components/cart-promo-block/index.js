import React from 'react';

import { applyRemovePromo } from '../../../utilities/update_cart';
import SectionTitle from '../../../utilities/section-title';
import dispatchCustomEvent from '../../../utilities/events';

export default class CartPromoBlock extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      promoApplied: false,
      buttonText: Drupal.t('apply'),
      disabled: false,
    };
  }

  componentDidMount() {
    const { coupon_code: couponCode } = this.props;

    if (couponCode.length > 0) {
      this.setState({
        promoApplied: true,
        buttonText: Drupal.t('applied'),
        disabled: true,
      });

      document.getElementById('promo-code').value = couponCode;
    }

    document.addEventListener('spcCartPromoError', this.cartPromoEventErrorHandler, false);
  }

  componentWillUnmount() {
    document.removeEventListener('spcCartPromoError', this.cartPromoEventErrorHandler, false);
  }

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

  promoAction = (promoApplied, inStock) => {
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
      });
    }
  };

  render() {
    const { promoApplied, disabled, buttonText } = this.state;
    const { inStock } = this.props;
    const promoRemoveActive = promoApplied ? 'active' : '';
    let disabledState = false;
    // Disable the promo field if out of stock or disabled.
    if (disabled === true || inStock === false) {
      disabledState = true;
    }

    return (
      <div className="spc-promo-code-block fadeInUp" style={{ animationDelay: '0.4s' }}>
        <SectionTitle>{Drupal.t('have a promo code?')}</SectionTitle>
        <div className="block-content">
          <input id="promo-code" disabled={disabledState} type="text" placeholder={Drupal.t('Promo code')} />
          <button id="promo-remove-button" type="button" className={`promo-remove ${promoRemoveActive}`} onClick={() => { this.promoAction(promoApplied, inStock); }}>{Drupal.t('Remove')}</button>
          <button id="promo-action-button" type="button" disabled={disabledState} className="promo-submit" onClick={() => { this.promoAction(promoApplied, inStock); }}>{buttonText}</button>
          <div id="promo-message" />
        </div>
      </div>
    );
  }
}
