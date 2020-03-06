import React from 'react';

import BillingPopUp from '../billing-popup';
import {
  gerAreaLabelById
} from '../../../utilities/address_util';

export default class BillingInfo extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      showPopup: false
    };
  }

  showPopup = () => {
    this.setState({
      showPopup: true
    });
  };

  render() {
    const { billing, shipping } = this.props;
    if (billing === undefined || billing == null) {
      return (null);
    }

    let addressData = [];
    Object.entries(drupalSettings.address_fields).forEach(([key, val]) => {
      if (billing[val.key] !== undefined) {
        let fillVal = billing[val.key];
        // Handling for area field.
        if (key === 'administrative_area') {
          fillVal = gerAreaLabelById(false, fillVal);
        }
        // Handling for parent area.
        else if (key === 'area_parent') {
          fillVal = gerAreaLabelById(true, fillVal);
        }
        addressData.push(fillVal);
      }
    })

    return (
      <React.Fragment>
        <div>
          <div>
            <div>{billing.firstname} {billing.lastname}</div>
            <div>{addressData.join(', ')}</div>
          </div>
          <div onClick={() => this.showPopup()}>{Drupal.t('change')}</div>
          {this.state.showPopup &&
            <BillingPopUp billing={billing} shipping={shipping}/>
          }
        </div>
      </React.Fragment>
    );

  }

}
