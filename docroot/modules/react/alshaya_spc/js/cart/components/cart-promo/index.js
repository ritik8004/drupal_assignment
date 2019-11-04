import React from 'react';

import axios from 'axios';

export default class CartPromo extends React.Component {

  promoAction = (action) => {
    var promo_value = document.getElementById('promo-code').value.trim();
    // If empty promo text.
    if (promo_value.length === 0) {
      document.getElementById('promo-message').innerHTML = Drupal.t('Please enter promo code.');
      return;
    }

    this.promoApplyRemove(action, promo_value);
  };

  promoApplyRemove = (action, code) => {
    var api_url = window.drupalSettings.alshaya_spc.middleware_url + '/cart/' + cart;

    return axios.get(api_url)
      .then(response => {
        return response.data
      })
      .catch(error => {
        // Processing of error here.
      });
  }

  render() {
    return <div>
        <div>{Drupal.t('Have a Promo Code?')}</div>
        <div>
          <div id='promo-message'></div>
          <input id='promo-code' type='text' placeholder={Drupal.t('Enter your promo code here')} />
          <button onClick={()=>{this.promoAction('apply')}}>{Drupal.t('Apply')}</button>
        </div>
      </div>
  }

}
