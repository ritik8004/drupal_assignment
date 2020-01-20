import React from 'react';

import ShippingMethod from '../shipping-method';
import SingleShippingMethod from '../single-shipping-method';
import {getShippingMethods} from '../../../utilities/checkout_util';

export default class ShippingMethods extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      'shipping_methods': []
    };
  }

  componentDidMount() {
    if (this.props.shipping_methods.length !== 0 &&
      this.props.cart.shipping_address !== null) {
      let address = this.prepareAddressObject(this.props.cart.shipping_address);
      let data = getShippingMethods(this.props.cart.cart_id, address);
      if (data instanceof Promise) {
        data.then((result) => {
          let methods = new Array();
          Object.entries(result).forEach(([key, method]) => {
            methods[key] = method;
          });

          this.setState({
            shipping_methods: methods
          });
        });
      }
    }
  }

  prepareAddressObject = (data) => {
    let address = {
      'country_id': data['country_id'],
      'custom_attributes': {}
    };

    Object.entries(window.drupalSettings.address_fields).forEach(([key, field]) => {
      address['custom_attributes'][field['key']] = data[field['key']];
    });

    return address;
  }

  render() {
    let methods = [];
    if (this.state.shipping_methods.length !== 0) {
      Object.entries(this.state.shipping_methods).forEach(([key, method]) => {
        methods.push(<ShippingMethod key={key} method={method}/>);
      });
    }

    // For single shipping method case.
    if (methods.length === 1) {
      return (
        <div className='shipping-methods'>
          <SingleShippingMethod method={this.state.shipping_methods[0]}/>
        </div>
      )
    }

    return (
      <div className='shipping-methods'>
      	{methods}
      </div>
    );
  }

}
