import React from 'react';
import AddressList from "../address-list";
import AddressForm from "../address-form";

export default class AddressContent extends React.Component {
  render() {
    if (window.drupalSettings.user.uid > 0) {
      return <AddressList/>;
    }
    else {
      return <AddressForm default_val={null} showEmail={true} processAddress={this.props.processAddress}/>;
    }
  }
};
