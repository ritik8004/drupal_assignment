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

  openModal = () => {
    this.setState({ open: true });
  };

  closeModal = () => {
    this.setState({ open: false });
  };


  eventListener = (e) => {
    const data = e.detail.data();
    const { refreshCart } = this.props;
    refreshCart(data);
    if (this.isComponentMounted) {
      this.closeModal();
    }
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
    const {
      cart: {
        delivery_type: deliveryType,
        cart: cartVal,
      },
      cart: mainCart,
    } = this.props;
    const { open } = this.state;

    if (deliveryType === 'cnc') {
      return (
        <div className="spc-empty-delivery-information">
          <div
            onClick={this.openModal}
            className="spc-checkout-empty-delivery-text"
          >
            {Drupal.t('select your preferred collection store')}
          </div>
          <Popup
            open={open}
            onClose={this.closeModal}
            closeOnDocumentClick={false}
          >
            <ClickCollectContainer closeModal={this.closeModal} />
          </Popup>
        </div>
      );
    }

    let defaultVal = null;
    // If logged in user.
    if (window.drupalSettings.user.uid > 0) {
      const { fname, lname } = window.drupalSettings.user_name;
      defaultVal = {
        static: {
          fullname: `${fname} ${lname}`,
        },
      };
    } else if (cartVal.carrier_info !== null
      && cartVal.shipping_address !== null) {
      // If carrier info set, means shipping is set.
      // Get name info from there.
      const shippingAddress = cartVal.shipping_address;
      defaultVal = {
        static: {
          fullname: `${shippingAddress.firstname} ${shippingAddress.lastname}`,
          email: shippingAddress.email,
          telephone: shippingAddress.telephone,
        },
      };
    }

    return (
      <div className="spc-empty-delivery-information">
        <div
          onClick={this.openModal}
          className="spc-checkout-empty-delivery-text"
        >
          {Drupal.t('please add your contact details and address.')}
        </div>
        <Popup
          className={getAddressPopupClassName()}
          open={open}
          onClose={this.closeModal}
          closeOnDocumentClick={false}
        >
          <React.Suspense fallback={<Loading />}>
            <AddressContent
              closeModal={this.closeModal}
              cart={mainCart}
              showEditButton
              headingText={Drupal.t('delivery information')}
              processAddress={this.processAddress}
              type="shipping"
              showEmail={window.drupalSettings.user.uid === 0}
              default_val={defaultVal}
            />
          </React.Suspense>
        </Popup>
      </div>
    );
  }
}
