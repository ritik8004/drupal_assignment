import React from 'react';

import SectionTitle from '../../../utilities/section-title';
import EmptyDeliveryText from '../empty-delivery';
import HomeDeliveryInfo from '../home-delivery';
import ClicknCollectDeiveryInfo from '../cnc-delivery';

export default class DeliveryInformation extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      'showEmpty': this.showEmpty(this.props)
    };
  }

  componentWillReceiveProps (nextProps) {
    this.setState({
      showEmpty: this.showEmpty(nextProps)
    });
  }

  showEmpty = (props) => {
    let showEmpty = true;
    if (props.cart.delivery_type === 'hd' &&
      props.cart.address !== undefined) {
      showEmpty = false;
    } else if (props.cart.delivery_type === 'cnc' &&
      props.cart.storeInfo !== undefined) {
      showEmpty = false;
    }

    return showEmpty;
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
        {!this.state.showEmpty && cart.delivery_type === 'cnc' &&
          <ClicknCollectDeiveryInfo cart={cart} refreshCart={this.props.refreshCart}/>
        }
      </div>
    );
  }

}
