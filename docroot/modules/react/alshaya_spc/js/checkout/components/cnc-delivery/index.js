import React from 'react';
import parse from 'html-react-parser';
import Popup from 'reactjs-popup';
import Loading from '../../../utilities/loading';
import ClickCollectContainer from '../click-collect';
import { cleanMobileNumber, removeFullScreenLoader, showFullScreenLoader } from '../../../utilities/checkout_util';
import createFetcher from '../../../utilities/api/fetcher';
import { fetchClicknCollectStores } from '../../../utilities/api/requests';
import { ClicknCollectContext } from '../../../context/ClicknCollect';
import WithModal from '../with-modal';
import dispatchCustomEvent from '../../../utilities/events';
import { makeFullName } from '../../../utilities/cart_customer_util';
import getStringMessage from '../../../utilities/strings';
import {
  isCollectionPoint,
  getPickUpPointTitle,
  getCncDeliveryTimePrefix,
} from '../../../utilities/cnc_util';
import ConditionalView from '../../../common/components/conditional-view';
import PriceElement from '../../../utilities/special-price/PriceElement';
import collectionPointsEnabled from '../../../../../js/utilities/pudoAramaxCollection';
import IctDeliveryInformation from '../online-booking/ict-delivery-information';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

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
      cartId,
    } = this.context;

    const fetchAgain = cartSelectedStore !== null
      && (cartSelectedStore.code !== selectedStore.code);

    if (storeList.length > 0
      && _.findIndex(storeList, { code: selectedStore.code }) > -1
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

    const args = {
      coords: fetchCoords,
      cartId,
    };
    const list = createFetcher(fetchClicknCollectStores).read(args);
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
          shipping: {
            address: shippingAddress, storeInfo: {
              name, address, open_hours_group: openHoursGroup, delivery_time: deliveryTime,
            },
          },
          ictDate, // Intercountry transfer delivery date from cart.
        },
      },
    } = this.props;

    let collectionPoint;
    let pudoAvailable;
    let pickupDate;
    let priceAmount;
    const { selectedStore } = this.context;

    if (collectionPointsEnabled()) {
      ({
        cart: {
          cart: {
            shipping: {
              collection_point: collectionPoint,
              pudo_available: pudoAvailable,
              pickup_date: pickupDate,
              price_amount: priceAmount,
            },
          },
        },
      } = this.props);
      pudoAvailable = pudoAvailable || isCollectionPoint(selectedStore);
    }

    const { showSelectedStore } = this.state;

    let hoursArray = [];

    if (collectionPointsEnabled()) {
      const hoursArrayList = [];
      if (openHoursGroup) {
        Object.keys(openHoursGroup).forEach((data) => {
          hoursArrayList.push(`${data}(${openHoursGroup[data]})`);
        });

        hoursArray = hoursArrayList.map((data) => (
          <div className="store-open-hours">
            {data}
          </div>
        ));
      }
    }

    return (
      <WithModal modalStatusKey="cncDelivery">
        {({ triggerOpenModal, triggerCloseModal, isModalOpen }) => (
          <div className="delivery-information-preview">
            <div className="spc-delivery-store-info">
              <div className="spc-delivery-store-name-wrapper">
                <ConditionalView condition={collectionPointsEnabled()}>
                  <span className={`${pudoAvailable ? 'collection-point' : 'store'}-icon`} />
                  <span className="pickup-point-title">{collectionPoint || getPickUpPointTitle(selectedStore)}</span>
                </ConditionalView>
                <div className="store-name">{name}</div>
              </div>
              <div className="store-address">
                {parse(address)}
              </div>
              <ConditionalView condition={collectionPointsEnabled()}>
                <div className="store-open-hours-list">
                  {hoursArray}
                </div>
                <div className="store-delivery-time">
                  <span className="label--delivery-time">{getStringMessage(getCncDeliveryTimePrefix())}</span>
                  <span className="delivery--time--value">{pickupDate || deliveryTime}</span>
                  {priceAmount
                    && <PriceElement amount={priceAmount} />}
                </div>
              </ConditionalView>
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
                { makeFullName(shippingAddress.firstname || '', shippingAddress.lastname || '') }
              </div>
              <div className="contact-telephone">
                {`+${drupalSettings.country_mobile_code} `}
                { cleanMobileNumber(shippingAddress.telephone) }
              </div>
              <div
                className="spc-change-address-link"
                onClick={() => this.openModal(true, triggerOpenModal)}
              >
                {Drupal.t('Edit')}
              </div>
            </div>
            { hasValue(ictDate) && (<IctDeliveryInformation deliveryMethod="click_and_collect" date={ictDate} />)}
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
