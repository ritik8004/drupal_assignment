import React from 'react';

import SectionTitle from '../../../utilities/section-title';
import EmptyDeliveryText from '../empty-delivery';
import HomeDeliveryInfo from '../home-delivery';
import ClicknCollectDeiveryInfo from '../cnc-delivery';

export default class DeliveryInformation extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      'showEmpty': this.showEmpty(this.props.cart)
    };
  }

  componentWillReceiveProps(nextProps) {
    this.setState({
      showEmpty: this.showEmpty(nextProps.cart)
    });
  }

  showEmpty = (cart) => {
    let delivery_type = (typeof cart.cart.delivery_type !== 'undefined')
      ? cart.cart.delivery_type
      : 'hd';

    let showEmpty = true;
    if (delivery_type === 'hd' && cart.cart.shipping_address !== null) {
      showEmpty = false;
    } else if (delivery_type === 'cnc' && typeof cart.cart.store_info !== 'undefined') {
      showEmpty = false;
    }
    return showEmpty;
  }

  render() {
    const { cart } = this.props;

    let title = cart.cart.delivery_type === 'cnc'
      ? Drupal.t('Collection store')
      : Drupal.t('Delivery information');

    return (
      <div className="spc-checkout-delivery-information">
        <SectionTitle>{title}</SectionTitle>
        {this.state.showEmpty &&
          <EmptyDeliveryText cart={cart} refreshCart={this.props.refreshCart} />
        }
        {!this.state.showEmpty && cart.cart.delivery_type === 'hd' &&
          <HomeDeliveryInfo cart={cart} refreshCart={this.props.refreshCart} />
        }
        {!this.state.showEmpty && cart.cart.delivery_type === 'cnc' &&
          <ClicknCollectDeiveryInfo cart={cart} refreshCart={this.props.refreshCart} />
        }
      </div>
    );
  }

}
