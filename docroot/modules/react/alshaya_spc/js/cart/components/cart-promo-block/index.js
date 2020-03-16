import React from 'react';

import { applyRemovePromo } from '../../../utilities/update_cart';
import SectionTitle from '../../../utilities/section-title';

export default class CartPromoBlock extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      promoApplied: false,
      promo_code: '',
      buttonText: Drupal.t('apply'),
      disabled: false,
    };
  }

  componentDidMount() {
    const { coupon_code } = this.props;

    if (coupon_code.length > 0) {
      this.setState({
        promoApplied: true,
        promo_code: coupon_code,
        buttonText: Drupal.t('applied'),
        disabled: true,
      });

      document.getElementById('promo-code').value = coupon_code;
    }
  }

  promoAction = (promoApplied) => {
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
              promo_code: promoValue,
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
              promo_code: '',
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
      });
    }
  };

  render() {
    const { promoApplied, disabled, buttonText } = this.state;
    const promoRemoveActive = promoApplied ? 'active' : '';
    return (
      <div className="spc-promo-code-block">
        <SectionTitle>{Drupal.t('have a promo code?')}</SectionTitle>
        <div className="block-content">
          <input id="promo-code" disabled={disabled} type="text" placeholder={Drupal.t('Promo code')} />
          <button id="promo-remove-button" type="button" className={`promo-remove ${promoRemoveActive}`} onClick={() => { this.promoAction(promoApplied); }}>{Drupal.t('Remove')}</button>
          <button id="promo-action-button" type="button" disabled={disabled} className="promo-submit" onClick={() => { this.promoAction(promoApplied); }}>{buttonText}</button>
          <div id="promo-message" />
        </div>
      </div>
    );
  }
}
