import React from 'react';
import Popup from 'reactjs-popup';
import Loading from '../../../utilities/loading';
import {
  checkoutAddressProcess,
  getAddressPopupClassName,
} from '../../../utilities/checkout_address_process';
import { addEditAddressToCustomer } from '../../../utilities/address_util';
import { showFullScreenLoader } from '../../../utilities/checkout_util';
import ClickCollectContainer from '../click-collect';
import Ifelse from '../../../common/components/if-else';

const AddressContent = React.lazy(() => import('../address-popup-content'));

export default class EmptyDeliveryText extends React.Component {
  isComponentMounted = false;

  constructor(props) {
    super(props);
    this.state = { open: false };
  }

  componentDidMount() {
    this.isComponentMounted = true;
    document.addEventListener(
      'refreshCartOnAddress',
      this.eventListener,
      false,
    );

    document.addEventListener(
      'refreshCartOnCnCSelect',
      this.eventListener,
      false,
    );

    document.addEventListener('closeAddressListPopup', this.closeModal, false);
  }

  componentWillUnmount() {
    this.isComponentMounted = false;
    document.removeEventListener(
      'refreshCartOnAddress',
      this.eventListener,
      false,
    );
    document.removeEventListener(
      'refreshCartOnCnCSelect',
      this.eventListener,
      false,
    );
  }

  eventListener = (e) => {
    const data = e.detail.data();
    const { refreshCart } = this.props;
    refreshCart(data);
    if (this.isComponentMounted) {
      this.closeModal();
    }
  };

  openModal = () => {
    this.setState({ open: true });
  };

  closeModal = () => {
    this.setState({ open: false });
  };

  /**
   * Process the address form data on sumbit.
   */
  processAddress = (e) => {
    // Show the loader.
    showFullScreenLoader();

    // If logged in user.
    if (window.drupalSettings.user.uid > 0) {
      addEditAddressToCustomer(e);
    } else {
      checkoutAddressProcess(e);
    }
  };

  render() {
    const { open } = this.state;
    const { cart: cartProp } = this.props;
    const { cart: { delivery_type: deliveryType, cart } } = this.props;
    const { updateCoordsAndStoreList } = this.context;

    let defaultVal = null;
    // If logged in user.
    if (drupalSettings.user.uid > 0) {
      const { fname, lname } = drupalSettings.user_name;
      defaultVal = {
        static: {
          fullname: `${fname} ${lname}`,
        },
      };
    } else if (cart.carrier_info !== null && cart.shipping_address !== null) {
      // If carrier info set, means shipping is set.
      // Get name info from there.
      const shippingAddress = cart.shipping_address;
      defaultVal = {
        static: {
          fullname: `${shippingAddress.firstname} ${shippingAddress.lastname}`,
          email: shippingAddress.email,
          telephone: shippingAddress.telephone,
        },
      };
    }

    const popup = (
      <Popup
        open={open}
        className={deliveryType === 'cnc' ? '' : getAddressPopupClassName()}
        onClose={this.closeModal}
        closeOnDocumentClick={false}
      >
        <Ifelse condition={deliveryType === 'cnc'}>
          <ClickCollectContainer
            closeModal={this.closeModal}
            onStoreFetch={updateCoordsAndStoreList}
          />
          <React.Suspense fallback={<Loading />}>
            <AddressContent
              closeModal={this.closeModal}
              cart={cartProp}
              showEditButton={true}
              headingText={Drupal.t('delivery information')}
              processAddress={this.processAddress}
              type="shipping"
              showEmail={drupalSettings.user.uid === 0}
              default_val={defaultVal}
            />
          </React.Suspense>
        </Ifelse>
      </Popup>
    );

    return (
      <div className="spc-empty-delivery-information">
        <div onClick={this.openModal} className="spc-checkout-empty-delivery-text">
          <Ifelse condition={deliveryType === 'cnc'}>
            {Drupal.t('select your preferred collection store')}
            {Drupal.t('please add your contact details and address.')}
          </Ifelse>
        </div>
        {popup}
      </div>
    );
  }
}
