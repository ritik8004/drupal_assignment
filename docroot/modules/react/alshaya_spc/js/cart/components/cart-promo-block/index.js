import React from 'react';

import {applyRemovePromo} from '../../../utilities/update_cart';
import CheckoutSectionTitle from "../spc-checkout-section-title";

export default class CartPromoBlock extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      'promo_applied': false,
      'promo_code': '',
      'button_text': Drupal.t('Apply')
    };
  }

  componentDidMount() {
    if (this.props.coupon_code.length > 0) {
      this.setState({
        promo_applied: true,
        promo_code: this.props.coupon_code,
        button_text: Drupal.t('Remove')
      });

      document.getElementById('promo-code').value = this.props.coupon_code;
    }
  }

  promoAction = (promo_applied) => {
    var promo_value = document.getElementById('promo-code').value.trim();
    // If empty promo text.
    if (this.state.promo_applied === false && promo_value.length === 0) {
      document.getElementById('promo-error-message').innerHTML = Drupal.t('Please enter promo code.');
      document.getElementById('promo-code').classList.add('error');
      return;
    }

    var action = (promo_applied === true) ? 'remove coupon' : 'apply coupon';

    var cart_data = applyRemovePromo(action, promo_value);
    if (cart_data instanceof Promise) {
      cart_data.then((result) => {
        // If coupon is not valid.
        if (result.response_message.status === 'error_coupon') {
          document.getElementById('promo-error-message').innerHTML = result.response_message.msg;
          document.getElementById('promo-code').classList.add('error');
        }
        else if(result.response_message.status === 'success') {
          document.getElementById('promo-error-message').innerHTML = result.response_message.msg;
          document.getElementById('promo-code').classList.add('success');
          // I initially promo_applied was false, means promo is applied now.
          if (promo_applied === false) {
            this.setState({
              promo_applied: true,
              promo_code: promo_value,
              button_text: Drupal.t('Remove')
            });
          }
          else {
            // It means promo is removed.
            this.setState({
              promo_applied: false,
              promo_code: '',
              button_text: Drupal.t('Apply')
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
    return (
      <div className="spc-promo-code-block">
        <CheckoutSectionTitle>{Drupal.t('have a promo code?')}</CheckoutSectionTitle>
        <div className="block-content">
          <input id="promo-code" type="text" placeholder={Drupal.t('Enter your promo code here')} />
          <button className="promo-submit" onClick={()=>{this.promoAction(this.state.promo_applied)}}>{this.state.button_text}</button>
          <div id="promo-error-message"/>
        </div>
      </div>
    );
  }

}
