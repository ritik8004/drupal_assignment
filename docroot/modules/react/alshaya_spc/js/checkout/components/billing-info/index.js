import React from 'react';

import Popup from 'reactjs-popup';
import Loading from '../../../utilities/loading';
import {
  getAddressPopupClassName,
  formatAddressDataForEditForm,
  processBillingUpdateFromForm,
  gerAreaLabelById,
} from '../../../utilities/address_util';
import WithModal from '../with-modal';
import dispatchCustomEvent from '../../../utilities/events';


const AddressContent = React.lazy(() => import('../address-popup-content'));

export default class BillingInfo extends React.Component {
  isComponentMounted = false;

  constructor(props) {
    super(props);
    this.billingUpdate = this.billingUpdate.bind(this);
  }


  componentDidMount() {
    this.isComponentMounted = true;
    document.addEventListener('onBillingAddressUpdate', this.billingUpdate);
  }

  componentWillUnmount() {
    this.isComponentMounted = false;
    document.removeEventListener('onBillingAddressUpdate', this.billingUpdate);
  }

  /**
   * Handle billing address update event.
   */
  billingUpdate(e) {
    if (this.isComponentMounted) {
      dispatchCustomEvent('closeModal', 'billingInfo');
    }
    const cart = e.detail;
    const { refreshCart } = this.props;
    refreshCart(cart);
  }

  /**
   * Process the billing address process.
   */
  processAddress = (e) => {
    const { cart } = this.props;
    return processBillingUpdateFromForm(e, cart.cart.shipping.address);
  }

  /**
   * Format address for edit address.
   */
  formatAddressData = (address) => (address === null
    ? address
    : formatAddressDataForEditForm(address))

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

        if (fillVal !== null) {
          addressData.push(fillVal);
        }
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
        <WithModal modalStatusKey="billingInfo">
          {({ triggerOpenModal, triggerCloseModal, isModalOpen }) => (
            <>
              <div className="spc-billing-change" onClick={() => triggerOpenModal()}>{Drupal.t('change')}</div>
              <Popup
                className={getAddressPopupClassName()}
                open={isModalOpen}
                closeOnEscape={false}
                closeOnDocumentClick={false}
              >
                <React.Suspense fallback={<Loading />}>
                  <AddressContent
                    closeModal={triggerCloseModal}
                    cart={cart}
                    processAddress={this.processAddress}
                    showEmail={false}
                    showEditButton={false}
                    type="billing"
                    formContext="billing"
                    headingText={Drupal.t('billing information')}
                    default_val={this.formatAddressData(billing)}
                  />
                </React.Suspense>
              </Popup>
            </>
          )}
        </WithModal>
      </div>
    );
  }
}
