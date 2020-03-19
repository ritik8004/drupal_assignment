import React from 'react';

import Popup from 'reactjs-popup';
import Loading from '../../../utilities/loading';
import {
  gerAreaLabelById,
} from '../../../utilities/address_util';
import {
  getAddressPopupClassName,
} from '../../../utilities/checkout_address_process';
import {
  processBillingUpdateFromForm,
} from '../../../utilities/checkout_address_process';

const AddressContent = React.lazy(() => import('../address-popup-content'));

export default class BillingInfo extends React.Component {
  isComponentMounted = false;

  constructor(props) {
    super(props);
    this.state = {
      open: false,
    };
  }

  componentWillUnmount() {
    this.isComponentMounted = false;
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

  /**
   * Process the billing address process.
   */
  processAddress = (e) => {
    const { cart } = this.props;
    return processBillingUpdateFromForm(e, cart.cart.shipping_address);
  }

  render() {
    const { cart } = this.props;
    const billing = cart.cart.billing_address;
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
        } else if (key === 'area_parent') {
          // Handling for parent area.
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
        <Popup
          className={getAddressPopupClassName()}
          open={this.state.open}
          onClose={this.closePopup}
          closeOnDocumentClick={false}
        >
          <React.Suspense fallback={<Loading />}>
            <AddressContent
              closeModal={this.closePopup}
              cart={this.props.cart}
              processAddress={this.processAddress}
              showEmail={false}
              showEditButton={false}
              type={'billing'}
              headingText={Drupal.t('billing information')}
              default_val={null}
            />
          </React.Suspense>
        </Popup>
      </div>
    );
  }
}
