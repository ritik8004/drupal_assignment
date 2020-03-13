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
  _isMounted = false;

  constructor(props) {
    super(props);
    this.state = { open: false };
  }

  openModal = () => {
    this.setState({ open: true });
  };

  closeModal = () => {
    this.setState({ open: false });
  };

  componentDidMount() {
    this._isMounted = true;
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
    this._isMounted = false;
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
    this.props.refreshCart(data);
    if (this._isMounted) {
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
      const { cart } = this.props.cart;
      checkoutAddressProcess(e, cart);
    }
  };

  render() {
    const { delivery_type, cart } = this.props.cart;

    if (delivery_type === 'cnc') {
      return (
        <div className="spc-empty-delivery-information">
          <div
            onClick={this.openModal}
            className="spc-checkout-empty-delivery-text"
          >
            {Drupal.t('select your preferred collection store')}
          </div>
          <Popup
            open={this.state.open}
            onClose={this.closeModal}
            closeOnDocumentClick={false}
          >
            <ClickCollectContainer closeModal={this.closeModal} />
          </Popup>
        </div>
      );
    }

    let default_val = null;
    // If logged in user.
    if (window.drupalSettings.user.uid > 0) {
      const { fname, lname } = window.drupalSettings.user_name;
      default_val = {
        static: {
          fullname: `${fname} ${lname}`,
        },
      };
    }
    // If carrier info set, means shipping is set.
    // Get name info from there.
    else if (cart.carrier_info !== null
      && cart.shipping_address !== null) {
      const shippingAddress = cart.shipping_address;
      default_val = {
        static: {
          fullname: shippingAddress.firstname + ' ' + shippingAddress.lastname,
          email: shippingAddress.email,
          telephone: shippingAddress.telephone
        }
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
          open={this.state.open}
          onClose={this.closeModal}
          closeOnDocumentClick={false}
        >
          <React.Suspense fallback={<Loading />}>
            <AddressContent
              closeModal={this.closeModal}
              cart={this.props.cart}
              processAddress={this.processAddress}
              show_prefered={window.drupalSettings.user.uid > 0}
              showEmail={window.drupalSettings.user.uid === 0}
              default_val={default_val}
            />
          </React.Suspense>
        </Popup>
      </div>
    );
  }
}
