import React from 'react';

import SectionTitle from '../../../utilities/section-title';
import HomeDeliverySVG from '../../../svg-component/hd-svg';
import ClickCollectSVG from '../../../svg-component/cc-svg';
import { smoothScrollTo } from '../../../utilities/smoothScroll';
import { isCnCEnabled } from '../../../utilities/checkout_util';

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

  // On delivery method change.
  changeDeliveryMethod = (method) => {
    const { cart, refreshCart } = this.props;
    // Not process click for cnc if disabled.
    if (!isCnCEnabled(cart.cart)
      && method === 'cnc') {
      return;
    }

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
    cart.delivery_type = method;
    refreshCart(cart);
    smoothScrollTo('.spc-checkout-delivery-information');
  }

  render() {
    const { cart } = this.props;
    const hdSubtitle = Drupal.t('Standard delivery for purchases over KD 250');
    const { selectedOption } = this.state;
    let cncSubtitle = window.drupalSettings.cnc_subtitle_available || '';

    const isCnCAvailable = isCnCEnabled(cart.cart);
    let cncInactiveClass = 'active';
    // If CnC disabled.
    if (!isCnCAvailable) {
      cncSubtitle = window.drupalSettings.cnc_subtitle_unavailable || '';
      cncInactiveClass = 'in-active';
    }

    return (
      <div className="spc-checkout-delivery-methods">
        <SectionTitle animationDelayValue="0.4s">{Drupal.t('Delivery method')}</SectionTitle>
        <div className="delivery-method fadeInUp" style={{ animationDelay: '0.6s' }} onClick={() => this.changeDeliveryMethod('hd')}>
          <input id="delivery-method-hd" defaultChecked={selectedOption === 'hd'} value="hd" name="delivery-method" type="radio" />
          <label className="radio-sim radio-label">
            <span className="icon"><HomeDeliverySVG /></span>
            <div className="delivery-method-name">
              <span className="impress">{Drupal.t('Home Delivery')}</span>
              <span className="sub-title">{hdSubtitle}</span>
            </div>
          </label>
        </div>
        <div className={`delivery-method fadeInUp ${cncInactiveClass}`} style={{ animationDelay: '0.7s' }} onClick={() => this.changeDeliveryMethod('cnc')}>
          <input id="delivery-method-cnc" defaultChecked={selectedOption === 'cnc'} disabled={isCnCAvailable ? false : 'disabled'} value="cnc" name="delivery-method" type="radio" />
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
