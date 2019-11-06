import React from 'react';
import Axios from 'axios';
import CheckoutSectionTitle from "../spc-checkout-section-title";

export default class CartPromoBlock extends React.Component {

  promoAction = (action) => {
    var promo_value = document.getElementById('promo-code').value.trim();
    // If empty promo text.
    if (promo_value.length === 0) {
      document.getElementById('promo-error-message').innerHTML = Drupal.t('Please enter promo code.');
      document.getElementById('promo-code').classList.add('error');
      return;
    }

    this.promoApplyRemove(action, promo_value);
  };

  promoApplyRemove = (action, code) => {
    let api_url = window.drupalSettings.alshaya_spc.middleware_url + '/cart/' + cart;

    return Axios.get(api_url)
      .then(response => {
        return response.data
      })
      .catch(error => {
        // Processing of error here.
      });
  };

  render() {
    return (
      <div className="spc-promo-code-block">
        <CheckoutSectionTitle>{Drupal.t('have a promo code?')}</CheckoutSectionTitle>
        <div className="block-content">
          <input id="promo-code" type="text" placeholder={Drupal.t('Enter your promo code here')} />
          <button className="promo-remove" onClick={()=>{this.promoAction('remove')}}/>
          <button className="promo-submit" onClick={()=>{this.promoAction('apply')}}>{Drupal.t('Apply')}</button>
          <div id="promo-error-message"/>
        </div>
      </div>
    );
  }

}
