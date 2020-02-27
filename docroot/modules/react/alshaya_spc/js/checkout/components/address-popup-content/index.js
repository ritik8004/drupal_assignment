import React from 'react';
import AddressList from "../address-list";
import AddressForm from "../address-form";

export default class AddressContent extends React.Component {

  render() {
    if (window.drupalSettings.user.uid > 0
      && window.drupalSettings.user_name.address_available) {
      return <AddressList/>;
    }
    else {
      return (
        <AddressForm
          show_prefered={this.props.show_prefered}
          default_val={this.props.default_val}
          showEmail={this.props.showEmail}
          processAddress={this.props.processAddress}
        />
      );
    }
  }
};
