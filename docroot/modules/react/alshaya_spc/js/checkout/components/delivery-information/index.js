import React from 'react';

import SectionTitle from '../../../utilities/section-title';
import EmptyDeliveryText from '../empty-delivery';
import HomeDeliveryInfo from '../home-delivery';

export default class DeliveryInformation extends React.Component {

  constructor(props) {
    super(props);

    let showEmpty = true;
    if (this.props.cart.delivery_type === 'hd'
      && this.props.cart.address !== undefined) {
        showEmpty = false;
    }
    else if(this.props.cart.delivery_type === 'cnc'
      && this.props.cart.storeInfo !== undefined) {
        showEmpty = false;
    }
    this.state = {
      'showEmpty': showEmpty
    };
  }

  render() {
    const { cart } = this.props;
    let title = cart.delivery_type === 'cnc'
  	  ? Drupal.t('Collection store')
      : Drupal.t('Delivery information');

    return (
      <div className="spc-checkout-delivery-information">
        <SectionTitle>{title}</SectionTitle>
        {this.state.showEmpty &&
          <EmptyDeliveryText cart={this.props.cart} refreshCart={this.props.refreshCart} />
        }
        {!this.state.showEmpty && cart.delivery_type === 'hd' &&
          <HomeDeliveryInfo cart={cart} refreshCart={this.props.refreshCart}/>
        }
      </div>
    );
  }

}
