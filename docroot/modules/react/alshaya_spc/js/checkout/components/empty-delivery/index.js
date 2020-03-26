import React from 'react';
import Popup from 'reactjs-popup';
import _isEmpty from 'lodash/isEmpty';
import _findKey from 'lodash/findKey';
import Loading from '../../../utilities/loading';
import {
  checkoutAddressProcess,
  getAddressPopupClassName,
  addEditAddressToCustomer,
} from '../../../utilities/address_util';
import {
  getDefaultMapCenter,
  getLocationAccess,
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../utilities/checkout_util';
import ClickCollectContainer from '../click-collect';
import { ClicknCollectContext } from '../../../context/ClicknCollect';
import createFetcher from '../../../utilities/api/fetcher';
import { fetchClicknCollectStores } from '../../../utilities/api/requests';

const AddressContent = React.lazy(() => import('../address-popup-content'));

export default class EmptyDeliveryText extends React.Component {
  isComponentMounted = false;

  static contextType = ClicknCollectContext;

  constructor(props) {
    super(props);
    this.state = {
      open: false,
    };
    this.openStoreRequests = [];
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

  getDeliveryType = () => {
    const {
      cart: {
        delivery_type: deliveryType,
      },
    } = this.props;

    return deliveryType;
  }

  cncEvent = () => {
    const { storeList } = this.context;

    if (this.getDeliveryType() !== 'cnc' || storeList.length > 0) {
      return;
    }

    const { fetchStoresHelper } = this;
    setTimeout(() => {
      if (window.fetchStore === 'idle') {
        fetchStoresHelper(getDefaultMapCenter(), true);
      }
    }, 200);

    getLocationAccess()
      .then((pos) => {
        fetchStoresHelper({
          lat: pos.coords.latitude,
          lng: pos.coords.longitude,
        });
      });
  }

  /**
   * Fetch click n collect stores and update store list.
   */
  fetchStoresHelper = (coords, defaultCenter = false) => {
    // State from context, whether the modal is open or not.
    const { clickCollectModal } = this.context;
    // Add all requests in array to update storeLists only once when
    // multiple requests are in progress.
    this.openStoreRequests.push({ coords, defaultCenter });

    if (_isEmpty(coords)) {
      window.fetchStore = 'finished';
      return;
    }

    window.fetchStore = 'pending';
    // When click n collect modal is loaded, we will show full screen loader.
    if (clickCollectModal) {
      showFullScreenLoader();
    }

    const list = createFetcher(fetchClicknCollectStores).read(coords);

    const { updateCoordsAndStoreList } = this.context;
    const { openStoreRequests } = this;
    // Set storeupdated to true, to avoid storeList update.
    // for two parallel requests (for default store fetch and for user location)
    // set storeupdated to true when storeList is updated, to avoid another update.
    let storeUpdated = false;
    list.then(
      (response) => {
        if (typeof response.error !== 'undefined' || storeUpdated) {
          window.fetchStore = 'finished';
          // When click n collect modal is loaded, we will have to remove full screen loader.
          if (clickCollectModal) {
            removeFullScreenLoader();
          }
        }

        // On two concurrent requests, update storelist only for user's location.
        if (openStoreRequests.length > 1) {
          const currentCoords = response.config.url.split('/').slice(-2).map((point) => parseFloat(point));
          const rquestIndex = _findKey(openStoreRequests, {
            coords: {
              lat: currentCoords[0],
              lng: currentCoords[1],
            },
          });

          const currentItem = openStoreRequests.splice(rquestIndex, 1);

          if (!currentItem.defaultCenter) {
            storeUpdated = true;
            updateCoordsAndStoreList(currentItem.coords, response.data);
          }
        } else {
          storeUpdated = true;
          updateCoordsAndStoreList(coords, response.data);
        }

        window.fetchStore = 'finished';

        // When click n collect modal is loaded, we will show full screen loader.
        if (clickCollectModal) {
          removeFullScreenLoader();
        }
      },
    );
  };

  openModal = () => {
    this.setState({ open: true });
    this.cncEvent();
  }

  closeModal = () => {
    this.setState({ open: false });

    if (this.getDeliveryType() !== 'cnc') {
      return;
    }

    const { updateModal } = this.context;
    updateModal(false);
  }

  eventListener = ({ detail }) => {
    const data = detail.data();
    const { refreshCart } = this.props;
    refreshCart(data);
    if (this.isComponentMounted) {
      this.closeModal();
    }
  }

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

    let defaultVal = null;
    // If logged in user.
    if (drupalSettings.user.uid > 0) {
      const { fname, lname } = drupalSettings.user_name;
      defaultVal = {
        static: {
          fullname: `${fname} ${lname}`,
        },
      };
    } else if (cartVal.carrier_info !== null && cartVal.shipping_address !== null) {
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

    const popup = (
      <Popup
        open={open}
        className={deliveryType === 'cnc' ? '' : getAddressPopupClassName()}
        onClose={this.closeModal}
        closeOnDocumentClick={false}
      >
        {deliveryType === 'cnc'
          ? (
            <ClickCollectContainer
              closeModal={this.closeModal}
            />
          )
          : (
            <React.Suspense fallback={<Loading />}>
              <AddressContent
                closeModal={this.closeModal}
                cart={mainCart}
                showEditButton
                headingText={Drupal.t('delivery information')}
                processAddress={this.processAddress}
                type="shipping"
                showEmail={drupalSettings.user.uid === 0}
                default_val={defaultVal}
              />
            </React.Suspense>
          )}
      </Popup>
    );

    return (
      <div className="spc-empty-delivery-information">
        <div onClick={this.openModal} className="spc-checkout-empty-delivery-text">
          {deliveryType === 'cnc'
            ? Drupal.t('select your preferred collection store')
            : Drupal.t('please add your contact details and address.')}
        </div>
        {popup}
      </div>
    );
  }
}
