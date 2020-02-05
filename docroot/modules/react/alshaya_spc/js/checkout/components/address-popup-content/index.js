import React from 'react';
import AddressList from "../address-list";
import AddressForm from "../address-form";

export default class AddressContent extends React.Component {
  render() {
    let uid = window.drupalSettings.user.uid;
    if (uid > 0) {
      return <AddressList/>;
    }
    else {
      return <AddressForm default_val={null} processAddress={this.processAddress}/>;
    }
  }
};
