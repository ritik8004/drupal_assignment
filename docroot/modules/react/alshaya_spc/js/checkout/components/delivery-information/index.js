import React from 'react';

import SectionTitle from '../../../utilities/section-title';
import EmptyDeliveryText from '../empty-delivery';
import HomeDeliveryInfo from '../home-delivery';
import {getShippingMethods} from '../../../utilities/checkout_util';

export default class DeliveryInformation extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      'areas': [],
      'empty': true,
      'hd_data': [],
      'cnc_data': [],
      'shipping_methods': []
    };
  }

  handleAddressData = (data) => {
    let temp_data = data;
    Object.entries(data).forEach(([key, val]) => {
      if (key !== 'static') {
        data[window.drupalSettings.address_fields[key].key] = val;
        delete data[key];
      }
    });

    // Get shipping methods.
    var shipping_methods = getShippingMethods(this.props.cart.cart_id, data);
    if (shipping_methods instanceof Promise) {
      shipping_methods.then((result) => {
        this.setState({
          empty: false,
          hd_data: data,
          shipping_methods: result
        });

        this.props.paymentMethodRefresh();
      });
    }
  }

  render() {
  	let title = this.props.delivery_type === 'cnc'
  	  ? Drupal.t('Collection store')
      : Drupal.t('Delivery information');

    return (
      <div className="spc-checkout-delivery-information">
        <SectionTitle>{title}</SectionTitle>
        {this.state.empty &&
          <EmptyDeliveryText handleAddressData={this.handleAddressData} cart={this.props.cart} delivery_type={this.props.delivery_type} />
        }
        {!this.state.empty && this.props.delivery_type === 'hd' &&
          <HomeDeliveryInfo handleAddressData={this.handleAddressData} hd_info={this.state.hd_data} methods={this.state.shipping_methods}/>
        }
      </div>
    );
  }

}
