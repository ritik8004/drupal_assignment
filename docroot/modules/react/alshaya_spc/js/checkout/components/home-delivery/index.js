import React from 'react';

import Popup from 'reactjs-popup';
import ShippingMethods from '../shipping-methods';
import Loading from '../../../utilities/loading';
import {
  showFullScreenLoader,
  cleanMobileNumber,
} from '../../../utilities/checkout_util';
import {
  checkoutAddressProcess,
  getAddressPopupClassName,
  formatAddressDataForEditForm,
  gerAreaLabelById,
  editDefaultAddressFromStorage,
} from '../../../utilities/address_util';
import WithModal from '../with-modal';
import dispatchCustomEvent from '../../../utilities/events';
import AreaConfirmationPopup from '../area-confirmation-popup';
import { isExpressDeliveryEnabled } from '../../../../../js/utilities/expressDeliveryHelper';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import { getDeliveryAreaStorage } from '../../../utilities/delivery_area_util';

const AddressContent = React.lazy(() => import('../address-popup-content'));

export default class HomeDeliveryInfo extends React.Component {
  isComponentMounted = false;

  constructor(props) {
    super(props);
    this.state = {
      areaUpdated: false,
    };
  }

  componentDidMount() {
    this.isComponentMounted = true;
    document.addEventListener('refreshCartOnAddress', this.eventListener);
    if (isExpressDeliveryEnabled()) {
      document.addEventListener('openAddressContentPopup', this.openAddressContentPopUp);
    }
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

  eventListener = (e) => {
    if (this.isComponentMounted) {
      dispatchCustomEvent('closeModal', 'hdInfo');
    }
    const { areaUpdated } = this.state;
    const data = e.detail;
    // Checking if delivery area selected matches with current address.
    if (areaUpdated) {
      dispatchCustomEvent('refreshAreaConfirmationState', data);
      this.setState({
        areaUpdated: false,
      });
    }
    const { refreshCart } = this.props;
    refreshCart(data);
  };

  /**
   * Format address for edit address.
   */
  formatAddressData = (address) => {
    const { areaUpdated } = this.state;
    const addressData = formatAddressDataForEditForm(address);
    let addressDataValue = { ...addressData };
    if (areaUpdated) {
      const areaSelected = getDeliveryAreaStorage();
      if (areaSelected !== null) {
        addressDataValue = editDefaultAddressFromStorage(addressData, areaSelected);
      }
    }
    return addressDataValue;
  }

  openAddressContentPopUp = (e) => {
    // Event openAddressContentPopUp is used by area-confirmation-popup
    // component and COD mobile verification component. If event detail has
    // enabledFieldWithMessage, then we don't need to update the area.
    if (e.detail && typeof e.detail.enabledFieldsWithMessages === 'undefined') {
      this.setState({
        areaUpdated: true,
      });
    }
  };

  render() {
    const {
      cart: { cart: { shipping: { address } } },
      cart: cartVal,
      refreshCart,
      isExpressDeliveryAvailable,
      shippingInfoUpdated,
    } = this.props;
    const { areaUpdated } = this.state;
    const addressData = [];
    Object.entries(drupalSettings.address_fields).forEach(([key, val]) => {
      if (address[val.key] !== undefined && val.visible === true) {
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
        <WithModal modalStatusKey="hdInfo" areaUpdated={areaUpdated}>
          {({
            triggerOpenModal, triggerCloseModal, isModalOpen, enabledFieldsWithMessages,
          }) => (
            <>
              <div className="spc-delivery-customer-info">
                <div className="delivery-name">
                  {address.firstname}
                  {' '}
                  {address.lastname}
                </div>
                <div className="delivery-email mobile-only-show">
                  {address.email}
                </div>
                <div className="delivery-mobile mobile-only-show">
                  {`+${drupalSettings.country_mobile_code} `}
                  { cleanMobileNumber(address.telephone) }
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
                    showEmail={drupalSettings.user.uid === 0}
                    default_val={
                      drupalSettings.user.uid === 0
                        ? this.formatAddressData(address)
                        : null
                    }
                    areaUpdated={areaUpdated}
                    isExpressDeliveryAvailable={isExpressDeliveryAvailable}
                    fillDefaultValue
                    enabledFieldsWithMessages={enabledFieldsWithMessages}
                  />
                </React.Suspense>
              </Popup>
            </>
          )}
        </WithModal>
        <div className="spc-delivery-shipping-methods">
          <ShippingMethods
            shippingInfoUpdated={shippingInfoUpdated}
            cart={cartVal}
            refreshCart={refreshCart}
          />
        </div>
        <ConditionalView condition={isExpressDeliveryEnabled()}>
          <AreaConfirmationPopup
            cart={cartVal}
            isExpressDeliveryAvailable={isExpressDeliveryAvailable}
          />
        </ConditionalView>
      </div>
    );
  }
}
