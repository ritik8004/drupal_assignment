import React from 'react';

import SectionTitle from '../../../utilities/section-title';
import HomeDeliverySVG from '../hd-svg';
import ClickCollectSVG from '../cc-svg';

export default class DeliveryMethods extends React.Component {
  constructor(props) {
    super(props);
    let deliveryType = 'hd';
    const { cart } = this.props;

    if (cart.delivery_type) {
      deliveryType = cart.delivery_type;
    } else if (cart.cart.delivery_type) {
      deliveryType = cart.cart.delivery_type;
    }

    this.state = {
      selectedOption: deliveryType,
    };
  }

  componentDidMount() {
    const { selectedOption } = this.state;
    const { cncEvent } = this.props;
    // Trigger cnc event to fetch stores.
    if (selectedOption === 'cnc') {
      cncEvent();
    }
  }

  // On delivery method change.
  changeDeliveryMethod = (method) => {
    this.setState({
      selectedOption: method,
    });

    document.getElementById(`delivery-method-${method}`).checked = true;
    const event = new CustomEvent('deliveryMethodChange', {
      bubbles: true,
      detail: {
        data: method,
      },
    });
    document.dispatchEvent(event);
    // Add delivery method in cart storage.
    const { cart, refreshCart, cncEvent } = this.props;
    cart.delivery_type = method;
    refreshCart(cart);
    // Trigger cnc event to fetch stores.
    if (method === 'cnc') {
      cncEvent();
    }
  }

  render() {
    const { cart: {cnc_disabled: cncDisabled}, cart} = this.props;
    const hdSubtitle = Drupal.t('Standard delivery for purchases over KD 250');
    const { selectedOption } = this.state;
    let cncSubtitle = window.drupalSettings.cnc_subtitle_available || '';

    // If CNC is disabled.
    if (cncDisabled) {
      cncSubtitle = window.drupalSettings.cnc_subtitle_unavailable || '';
    }

    return (
      <div className="spc-checkout-delivery-methods">
        <SectionTitle>{Drupal.t('Delivery method')}</SectionTitle>
        <div className="delivery-method" onClick={() => this.changeDeliveryMethod('hd')}>
          <input id="delivery-method-hd" defaultChecked={selectedOption === 'hd'} value="hd" name="delivery-method" type="radio" />
          <label className="radio-sim radio-label">
            <span className="icon"><HomeDeliverySVG /></span>
            <div className="delivery-method-name">
              <span className="impress">{Drupal.t('Home Delivery')}</span>
              <span className="sub-title">{hdSubtitle}</span>
            </div>
          </label>
        </div>
        <div className="delivery-method" onClick={() => this.changeDeliveryMethod('cnc')}>
          <input id="delivery-method-cnc" defaultChecked={selectedOption === 'cnc'} disabled={cncDisabled} value="cnc" name="delivery-method" type="radio" />
          <label className="radio-sim radio-label">
            <span className="icon"><ClickCollectSVG /></span>
            <div className="delivery-method-name">
              <span className="impress">{Drupal.t('Click & Collect')}</span>
              <span className="sub-title">{cncSubtitle}</span>
            </div>
          </label>
        </div>
      </div>
    );
  }
}
