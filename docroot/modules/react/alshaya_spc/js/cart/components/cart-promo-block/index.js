import React from 'react';

import { applyRemovePromo } from '../../../utilities/update_cart';
import SectionTitle from '../../../utilities/section-title';

export default class CartPromoBlock extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      promo_applied: false,
      promo_code: '',
      button_text: Drupal.t('apply'),
      disabled: false,
    };
  }

  componentDidMount() {
    if (this.props.coupon_code.length > 0) {
      this.setState({
        promo_applied: true,
        promo_code: this.props.coupon_code,
        button_text: Drupal.t('applied'),
        disabled: true,
      });

      document.getElementById('promo-code').value = this.props.coupon_code;
    }
  }

  promoAction = (promo_applied) => {
    const promo_value = document.getElementById('promo-code').value.trim();
    // If empty promo text.
    if (this.state.promo_applied === false && promo_value.length === 0) {
      document.getElementById('promo-message').innerHTML = Drupal.t('please enter promo code.');
      document.getElementById('promo-message').classList.add('error');
      document.getElementById('promo-code').classList.add('error');
      return;
    }

    const action = (promo_applied === true) ? 'remove coupon' : 'apply coupon';

    // Adding class on promo button for showing progress when click and applying promo.
    if (promo_applied !== true) {
      document.getElementById('promo-action-button').classList.add('loading');
    } else {
      document.getElementById('promo-remove-button').classList.add('loading');
    }

    const cart_data = applyRemovePromo(action, promo_value);
    if (cart_data instanceof Promise) {
      cart_data.then((result) => {
        // Removing button clicked class.
        document.getElementById('promo-action-button').classList.remove('loading');
        document.getElementById('promo-remove-button').classList.remove('loading');
        // If coupon is not valid.
        if (result.response_message.status === 'error_coupon') {
          const event = new CustomEvent('promoCodeFailed', { bubbles: true, detail: { data: promo_value } });
          document.getElementById('promo-message').innerHTML = result.response_message.msg;
          document.getElementById('promo-message').classList.add('error');
          document.getElementById('promo-code').classList.add('error');
          // Dispatch event promoCodeFailed for GTM.
          document.dispatchEvent(event);
        }
        else if(result.response_message.status === 'success') {
          document.getElementById('promo-message').innerHTML = '';
          // Initially promo_applied was false, means promo is applied now.
          if (promo_applied === false) {
            this.setState({
              promo_applied: true,
              promo_code: promo_value,
              button_text: Drupal.t('applied'),
              disabled: true
            });
            // Dispatch event promoCodeSuccess for GTM.
            var event = new CustomEvent('promoCodeSuccess', {bubbles: true, detail: { data: promo_value }});
            document.dispatchEvent(event);
          }
          else {
            // It means promo is removed.
            this.setState({
              promo_applied: false,
              promo_code: '',
              button_text: Drupal.t('apply'),
              disabled: false
            });

            document.getElementById('promo-code').value = '';
          }

          // Refreshing mini-cart.
          var event = new CustomEvent('refreshMiniCart', {bubbles: true, detail: { data: () => result }});
          document.dispatchEvent(event);

          // Refreshing cart components..
          var event = new CustomEvent('refreshCart', {bubbles: true, detail: { data: () => result }});
          document.dispatchEvent(event);
        }
      });
    }
  };

  render() {
    var promo_remove_active = this.state.promo_applied ? 'active' : '';
    return (
      <div className="spc-promo-code-block">
        <SectionTitle>{Drupal.t('have a promo code?')}</SectionTitle>
        <div className="block-content">
          <input id="promo-code" disabled={this.state.disabled} type="text" placeholder={Drupal.t('Promo code')} />
          <button id="promo-remove-button" className={"promo-remove " + promo_remove_active} onClick={()=>{this.promoAction(this.state.promo_applied)}}>{Drupal.t('Remove')}</button>
          <button id="promo-action-button" disabled={this.state.disabled} className="promo-submit" onClick={()=>{this.promoAction(this.state.promo_applied)}}>{this.state.button_text}</button>
          <div id="promo-message"/>
        </div>
      </div>
    );
  }

}
