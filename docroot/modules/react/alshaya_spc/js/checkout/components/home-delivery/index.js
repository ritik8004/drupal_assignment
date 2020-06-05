import React from 'react';

import Popup from 'reactjs-popup';
import ShippingMethods from '../shipping-methods';
import Loading from '../../../utilities/loading';
import {
  showFullScreenLoader,
} from '../../../utilities/checkout_util';
import {
  checkoutAddressProcess,
  getAddressPopupClassName,
  formatAddressDataForEditForm,
  gerAreaLabelById,
} from '../../../utilities/address_util';
import WithModal from '../with-modal';
import dispatchCustomEvent from '../../../utilities/events';

const AddressContent = React.lazy(() => import('../address-popup-content'));

export default class HomeDeliveryInfo extends React.Component {
  isComponentMounted = false;

  constructor(props) {
    super(props);
    this.eventListener = this.eventListener.bind(this);
  }

  componentDidMount() {
    this.isComponentMounted = true;
    document.addEventListener('refreshCartOnAddress', this.eventListener);
  }

  componentWillUnmount() {
    this.isComponentMounted = false;
    document.removeEventListener('refreshCartOnAddress', this.eventListener);
  }

  processAddress = (e) => {
    // Show the loader.
    showFullScreenLoader();
    checkoutAddressProcess(e);
  };

  eventListener(e) {
    if (this.isComponentMounted) {
      dispatchCustomEvent('closeModal', 'hdInfo');
    }
    const data = e.detail;
    const { refreshCart } = this.props;
    refreshCart(data);
  }

  /**
   * Format address for edit address.
   */
  formatAddressData = (address) => formatAddressDataForEditForm(address)

  render() {
    const {
      cart: { cart: { shipping: { address } } },
      cart: cartVal,
      refreshCart,
    } = this.props;
    const addressData = [];
    Object.entries(window.drupalSettings.address_fields).forEach(([key, val]) => {
      if (address[val.key] !== undefined) {
        let fillVal = address[val.key];
        // Handling for area field.
        if (key === 'administrative_area') {
          fillVal = gerAreaLabelById(false, fillVal);
        } else if (key === 'area_parent') {
          fillVal = gerAreaLabelById(true, fillVal);
        }

        if (fillVal !== null) {
          addressData.push(fillVal);
        }
      }
    });

    return (
      <div className="delivery-information-preview">
        <WithModal modalStatusKey="hdInfo">
          {({ triggerOpenModal, triggerCloseModal, isModalOpen }) => (
            <>
              <div className="spc-delivery-customer-info">
                <div className="delivery-name">
                  {address.firstname}
                  {' '}
                  {address.lastname}
                </div>
                <div className="delivery-address">
                  {addressData.join(', ')}
                </div>
                <div className="spc-address-form-edit-link" onClick={() => triggerOpenModal()}>
                  {Drupal.t('Change')}
                </div>
              </div>
              <Popup
                open={isModalOpen}
                closeOnEscape={false}
                closeOnDocumentClick={false}
                className={getAddressPopupClassName()}
              >
                <React.Suspense fallback={<Loading />}>
                  <AddressContent
                    cart={cartVal}
                    closeModal={() => triggerCloseModal()}
                    processAddress={this.processAddress}
                    showEditButton
                    headingText={Drupal.t('delivery information')}
                    type="shipping"
                    showEmail={window.drupalSettings.user.uid === 0}
                    default_val={
                      window.drupalSettings.user.uid === 0
                        ? this.formatAddressData(address)
                        : null
                    }
                  />
                </React.Suspense>
              </Popup>
            </>
          )}
        </WithModal>
        <div className="spc-delivery-shipping-methods">
          <ShippingMethods
            cart={cartVal}
            refreshCart={refreshCart}
          />
        </div>
      </div>
    );
  }
}
