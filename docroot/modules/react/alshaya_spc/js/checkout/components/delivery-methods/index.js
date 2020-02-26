import React from 'react';

import SectionTitle from '../../../utilities/section-title';

export default class DeliveryMethods extends React.Component {

  constructor(props) {
    super(props);
    let delivery_type = 'hd';

    if (this.props.cart.delivery_type) {
      delivery_type = this.props.cart.delivery_type;
    }
    else if (this.props.cart.cart.delivery_type) {
      delivery_type = this.props.cart.cart.delivery_type;
    }

    this.state = {
      'selectedOption': delivery_type
    };
  }

  // On delivery method change.
  changeDeliveryMethod = (method) => {
    this.setState({
      selectedOption: method
    });

    document.getElementById('delivery-method-' + method).checked = true;
    // Add delivery method in cart storage.
    let cart = this.props.cart;
    cart['delivery_type'] = method;
    this.props.refreshCart(cart);
  }

  render() {
    const { cnc_disabled } = this.props.cart;
    let hd_subtitle = Drupal.t('Standard delivery for purchases over KD 250');
    let cnc_subtitle = window.drupalSettings.cnc_subtitle_available || '';

    // If CNC is disabled.
    if (cnc_disabled) {
      cnc_subtitle = window.drupalSettings.cnc_subtitle_unavailable || '';
    }

    return (
      <div className='spc-checkout-delivery-methods'>
        <SectionTitle>{Drupal.t('delivery method')}</SectionTitle>
        <div className='delivery-method' onClick={() => this.changeDeliveryMethod('hd')}>
          <input id='delivery-method-hd' defaultChecked={this.state.selectedOption === 'hd'} value='hd' name='delivery-method' type='radio' />
          <label className='radio-sim radio-label'>
            <span className='icon'></span>
            <span className='impress'>{Drupal.t('home delivery')}</span>
            <span className='sub-title'>{hd_subtitle}</span>
          </label>
        </div>
        <div className='delivery-method' onClick={() => this.changeDeliveryMethod('cnc')}>
          <input id='delivery-method-cnc' defaultChecked={this.state.selectedOption === 'cnc'} disabled={cnc_disabled} value='cnc' name='delivery-method' type='radio' />
          <label className='radio-sim radio-label'>
            <span className='icon'></span>
            <span className='impress'>{Drupal.t('click & collect')}</span>
            <span className='sub-title'>{cnc_subtitle}</span>
          </label>
        </div>
      </div>
    )
  }
}
