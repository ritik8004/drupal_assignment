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
import WithModal from '../with-modal';
import dispatchCustomEvent from '../../../utilities/events';

class ClicknCollectDeiveryInfo extends React.Component {
  isComponentMounted = true;

  static contextType = ClicknCollectContext;

  constructor(props) {
    super(props);
    this.state = {
      showSelectedStore: false,
    };
  }

  componentDidMount() {
    this.isComponentMounted = true;
    this.fetchStoresHelper();
    document.addEventListener('refreshCartOnCnCSelect', this.eventListener);
  }

  componentWillUnmount() {
    this.isComponentMounted = false;
    document.removeEventListener('refreshCartOnCnCSelect', this.eventListener);
  }

  openModal = (showSelectedStore, callback) => {
    this.setState({
      showSelectedStore: showSelectedStore || false,
    });
    this.fetchStoresHelper();
    callback();
  };

  eventListener = ({ detail }) => {
    if (this.isComponentMounted) {
      dispatchCustomEvent('closeModal', 'cncDelivery');
    }
    const data = detail;
    const { refreshCart } = this.props;
    refreshCart(data);
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
          shipping: { address: shippingAddress, storeInfo: { name, address } },
        },
      },
    } = this.props;

    const { showSelectedStore } = this.state;

    return (
      <WithModal modalStatusKey="cncDelivery">
        {({ triggerOpenModal, triggerCloseModal, isModalOpen }) => (
          <div className="delivery-information-preview">
            <div className="spc-delivery-store-info">
              <div className="store-name">{name}</div>
              <div className="store-address">
                {parse(address)}
              </div>
              <div
                className="spc-change-address-link"
                onClick={() => this.openModal(false, triggerOpenModal)}
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
                onClick={() => this.openModal(true, triggerOpenModal)}
              >
                {Drupal.t('Edit')}
              </div>
            </div>
            <Popup
              open={isModalOpen}
              closeOnEscape={false}
              closeOnDocumentClick={false}
            >
              <React.Suspense fallback={<Loading />}>
                <ClickCollectContainer
                  closeModal={() => triggerCloseModal()}
                  openSelectedStore={showSelectedStore}
                />
              </React.Suspense>
            </Popup>
          </div>
        )}
      </WithModal>
    );
  }
}

export default ClicknCollectDeiveryInfo;
