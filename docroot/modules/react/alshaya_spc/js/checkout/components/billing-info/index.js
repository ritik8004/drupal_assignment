import React from 'react';

import BillingPopUp from '../billing-popup';
import {
  gerAreaLabelById,
} from '../../../utilities/address_util';

export default class BillingInfo extends React.Component {
  _isMounted = false;

  constructor(props) {
    super(props);
    this.state = {
      open: false,
    };
  }

  showPopup = () => {
    this.setState({
      open: true,
    });
  };

  closePopup = () => {
    this.setState({
      open: false,
    });
  };

  componentWillUnmount() {
    this._isMounted = false;
  }

  render() {
    const { billing, shipping } = this.props;
    if (billing === undefined || billing == null) {
      return (null);
    }

    const addressData = [];
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
    });

    return (
      <div className="spc-billing-information">
        <div className="spc-billing-meta">
          <div className="spc-billing-name">
            {billing.firstname}
            {' '}
            {billing.lastname}
          </div>
          <div className="spc-billing-address">{addressData.join(', ')}</div>
        </div>
        <div className="spc-billing-change" onClick={() => this.showPopup()}>{Drupal.t('change')}</div>
        {this.state.open
          && <BillingPopUp closePopup={this.closePopup} billing={billing} shipping={shipping} />}
      </div>
    );
  }
}
