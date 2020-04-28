import React from 'react';
import parse from 'html-react-parser';
import Popup from 'reactjs-popup';
import _findIndex from 'lodash/findIndex';
import Loading from '../../../utilities/loading';
import ClickCollectContainer from '../click-collect';
import { cleanMobileNumber, removeFullScreenLoader, showFullScreenLoader } from '../../../utilities/checkout_util';
import createFetcher from '../../../utilities/api/fetcher';
import { fetchClicknCollectStores } from '../../../utilities/api/requests';
import { ClicknCollectContext } from '../../../context/ClicknCollect';

class ClicknCollectDeiveryInfo extends React.Component {
  isComponentMounted = true;

  static contextType = ClicknCollectContext;

  constructor(props) {
    super(props);
    this.state = {
      open: false,
      showSelectedStore: false,
    };
  }

  componentDidMount() {
    this.isComponentMounted = true;
    document.addEventListener(
      'refreshCartOnCnCSelect',
      this.eventListener,
      false,
    );
    this.fetchStoresHelper();
  }

  componentWillUnmount() {
    this.isComponentMounted = false;
    document.removeEventListener(
      'refreshCartOnCnCSelect',
      this.eventListener,
      false,
    );
  }

  openModal = (showSelectedStore) => {
    this.setState({
      open: true,
      showSelectedStore: showSelectedStore || false,
    });
    this.fetchStoresHelper();
  };

  closeModal = () => {
    this.setState({ open: false });
  };

  eventListener = ({ detail }) => {
    const data = detail;
    const { refreshCart } = this.props;
    refreshCart(data);
    if (this.isComponentMounted) {
      this.closeModal();
    }
  };

  /**
   * Fetch click n collect stores and update store list.
   */
  fetchStoresHelper = () => {
    const {
      clickCollectModal,
      coords,
      storeList,
      selectedStore,
      updateCoordsAndStoreList,
      cartSelectedStore,
      updateSelectStore,
      showOutsideCountryError,
    } = this.context;

    const fetchAgain = cartSelectedStore !== null
      && (cartSelectedStore.code !== selectedStore.code);

    if (storeList.length > 0
      && _findIndex(storeList, { code: selectedStore.code }) > -1
      && !fetchAgain) {
      return;
    }

    let fetchCoords = coords;
    if (storeList.length > 0 || fetchAgain) {
      if (fetchAgain) {
        updateSelectStore(cartSelectedStore);
      }
      fetchCoords = { lat: cartSelectedStore.lat, lng: cartSelectedStore.lng };
    }

    window.fetchStore = 'pending';
    // When click n collect modal is loaded, we will show full screen loader.
    if (clickCollectModal) {
      showFullScreenLoader();
    }

    const list = createFetcher(fetchClicknCollectStores).read(fetchCoords);
    list.then(
      (response) => {
        if (typeof response.error !== 'undefined') {
          window.fetchStore = 'finished';
          // When click n collect modal is loaded, we will have to remove full screen loader.
          if (clickCollectModal) {
            removeFullScreenLoader();
          }
        }

        showOutsideCountryError(false);
        updateCoordsAndStoreList(fetchCoords, response.data);
        window.fetchStore = 'finished';

        // When click n collect modal is loaded, we will have to remove full screen loader.
        if (clickCollectModal) {
          removeFullScreenLoader();
        }
      },
    );
  };

  render() {
    const {
      cart: {
        cart: {
          store_info: { name, address },
          shipping: { address: shippingAddress },
        },
      },
    } = this.props;

    const { open, showSelectedStore } = this.state;

    return (
      <div className="delivery-information-preview">
        <div className="spc-delivery-store-info">
          <div className="store-name">{name}</div>
          <div className="store-address">
            {parse(address)}
          </div>
          <div
            className="spc-change-address-link"
            onClick={() => this.openModal(false)}
          >
            {Drupal.t('Change')}
          </div>
        </div>
        <div className="spc-delivery-contact-info">
          <div className="contact-info-label">{Drupal.t('Collection by')}</div>
          <div className="contact-name">
            {`${shippingAddress.firstname} ${shippingAddress.lastname}`}
          </div>
          <div className="contact-telephone">{`+${drupalSettings.country_mobile_code} ${cleanMobileNumber(shippingAddress.telephone)}`}</div>
          <div
            className="spc-change-address-link"
            onClick={() => this.openModal(true)}
          >
            {Drupal.t('Edit')}
          </div>
        </div>
        <Popup
          open={open}
          onClose={this.closeModal}
          closeOnDocumentClick={false}
        >
          <React.Suspense fallback={<Loading />}>
            <ClickCollectContainer
              closeModal={this.closeModal}
              openSelectedStore={showSelectedStore}
            />
          </React.Suspense>
        </Popup>
      </div>
    );
  }
}

export default ClicknCollectDeiveryInfo;
