import React from 'react';

import SectionTitle from '../../../utilities/section-title';
import EmptyDeliveryText from '../empty-delivery';
import HomeDeliveryInfo from '../home-delivery';
import {getShippingMethods} from '../../../utilities/checkout_util';
import {addShippingInCart} from '../../../utilities/update_cart';

export default class DeliveryInformation extends React.Component {

  constructor(props) {
    super(props);
    let empty = true;
    let hd_data = [];
    let shipping_methods = [];
    if (this.props.cart.delivery_method !== null) {
      if (this.props.cart.delivery_method === 'hd' &&
      this.props.cart.shipping_address !== null) {
        hd_data = this.props.cart.shipping_address;
        empty = false;
        shipping_methods = this.props.cart.carrier_info;
      }
    }

    this.state = {
      'areas': [],
      'empty': empty,
      'hd_data': hd_data,
      'cnc_data': [],
      'shipping_methods': shipping_methods
    };
  }

  handleAddressData = (data) => {
    Object.entries(data).forEach(([key, val]) => {
      if (key !== 'static') {
        data[window.drupalSettings.address_fields[key].key] = val;
        delete data[key];
      }
    });

    let temp_data = JSON.parse(JSON.stringify(data));;
    let static_data = temp_data['static'];
    delete temp_data['static'];
    let add_data = {...temp_data, ...static_data};
    // Get shipping methods.
    var shipping_methods = getShippingMethods(this.props.cart.cart_id, data);
    if (shipping_methods instanceof Promise) {
      shipping_methods.then((result) => {
        this.setState({
          empty: false,
          hd_data: add_data,
          shipping_methods: result
        });

        data['carrier_info'] = {
          'code': result[0].carrier_code,
          'method': result[0].method_code
        };
        var cart = addShippingInCart('update shipping', data);
        if (cart instanceof Promise) {
          cart.then((cart_result) => {
            this.props.paymentMethodRefresh();
            this.props.refreshCart(cart_result);
          });
        }
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
          <HomeDeliveryInfo cart={this.props.cart} handleAddressData={this.handleAddressData} hd_info={this.state.hd_data} methods={this.state.shipping_methods}/>
        }
      </div>
    );
  }

}
