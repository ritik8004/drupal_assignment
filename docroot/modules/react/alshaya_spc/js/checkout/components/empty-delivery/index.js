import React from 'react';
import Popup from 'reactjs-popup';
import Loading from '../../../utilities/loading';
import {
  checkoutAddressProcess,
  getAddressPopupClassName,
  addEditAddressToCustomer, customerHasAddress,
} from '../../../utilities/address_util';
import {
  getDefaultMapCenter,
  getLocationAccess,
  removeFullScreenLoader,
  showFullScreenLoader,
  getCnCStoresLimit,
} from '../../../utilities/checkout_util';
import ClickCollectContainer from '../click-collect';
import { ClicknCollectContext } from '../../../context/ClicknCollect';
import createFetcher from '../../../utilities/api/fetcher';
import { fetchClicknCollectStores } from '../../../utilities/api/requests';
import { getUserLocation } from '../../../utilities/map/map_utils';
import dispatchCustomEvent from '../../../utilities/events';
import WithModal from '../with-modal';
import { makeFullName } from '../../../utilities/cart_customer_util';
import {
  getCncSectionDescription,
} from '../../../utilities/cnc_util';
import { isExpressDeliveryEnabled } from '../../../../../js/utilities/expressDeliveryHelper';
import { getDeliveryAreaStorage } from '../../../utilities/delivery_area_util';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { isMobile } from '../../../../../js/utilities/display';

const AddressContent = React.lazy(() => import('../address-popup-content'));

export default class EmptyDeliveryText extends React.Component {
  isComponentMounted = false;

  static contextType = ClicknCollectContext;

  constructor(props) {
    super(props);
    this.openStoreRequests = [];
  }

  componentDidMount() {
    this.isComponentMounted = true;
    document.addEventListener('refreshCartOnAddress', this.eventListener);
    document.addEventListener('refreshCartOnCnCSelect', this.eventListener);
    document.addEventListener('closeAddressListPopup', this.eventClosePopup);
  }

  componentWillUnmount() {
    this.isComponentMounted = false;
    document.removeEventListener('refreshCartOnAddress', this.eventListener);
    document.removeEventListener('refreshCartOnCnCSelect', this.eventListener);
    document.removeEventListener('closeAddressListPopup', this.eventClosePopup);
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
    const { storeList, updateLocationAccess, showOutsideCountryError } = this.context;

    if (this.getDeliveryType() !== 'click_and_collect' || storeList.length > 0) {
      return;
    }

    const { fetchStoresHelper } = this;
    setTimeout(() => {
      if (window.fetchStore === 'idle') {
        fetchStoresHelper(getDefaultMapCenter(), true, getCnCStoresLimit());
      }
    }, 200);

    getLocationAccess()
      .then(
        async (pos) => {
          const coords = {
            lat: pos.coords.latitude,
            lng: pos.coords.longitude,
          };
          try {
            const [userCountrySame] = await getUserLocation(coords);
            // If user and site country not same, don;t process.
            if (!userCountrySame) {
              removeFullScreenLoader();
              // Trigger event to update.
              showOutsideCountryError(true);
              return;
            }
          } catch (error) {
            Drupal.logJavascriptError('clickncollect-checkUserCountry', error, GTM_CONSTANTS.CHECKOUT_ERRORS);
          }
          fetchStoresHelper(coords);
        },
        () => {
          updateLocationAccess(false);
        },
      )
      .catch((error) => {
        removeFullScreenLoader();
        Drupal.logJavascriptError('clickncollect-getCurrentPosition', error, GTM_CONSTANTS.CHECKOUT_ERRORS);
      });
  }

  /**
   * Fetch click n collect stores and update store list.
   */
  fetchStoresHelper = (coords, defaultCenter = false, cncStoresLimit = 0) => {
    // State from context, whether the modal is open or not.
    const { clickCollectModal, showOutsideCountryError, cartId } = this.context;
    // Add all requests in array to update storeLists only once when
    // multiple requests are in progress.
    this.openStoreRequests.push({ coords, defaultCenter });

    if (!hasValue(coords)) {
      window.fetchStore = 'finished';
      return;
    }

    window.fetchStore = 'pending';
    // When click n collect modal is loaded, we will show full screen loader.
    if (clickCollectModal) {
      showFullScreenLoader();
    }

    const args = {
      coords,
      cartId,
      cncStoresLimit,
    };
    const list = createFetcher(fetchClicknCollectStores).read(args);

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
        if (openStoreRequests.length > 1 && response.config) {
          const currentCoords = response.config.url.split('/').slice(-2).map((point) => parseFloat(point));
          const rquestIndex = _.findKey(openStoreRequests, {
            coords: {
              lat: currentCoords[0],
              lng: currentCoords[1],
            },
          });

          const currentItem = openStoreRequests.splice(rquestIndex, 1);

          if (!currentItem.defaultCenter) {
            storeUpdated = true;
            showOutsideCountryError(false);
            updateCoordsAndStoreList(currentItem.coords, response.data, true);
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

  openModal = (callback) => {
    callback();
    this.cncEvent();
  }

  closeModal = (callback) => {
    callback();
    if (this.getDeliveryType() === 'click_and_collect') {
      const { updateModal } = this.context;
      updateModal(false);
    }
  }

  eventListener = (e) => {
    this.eventClosePopup();
    const data = e.detail;
    const { refreshCart } = this.props;
    refreshCart(data);
  }

  eventClosePopup = () => {
    if (this.isComponentMounted) {
      dispatchCustomEvent('closeModal', 'deliveryType');
    }
  }

  /**
   * Process the address form data on sumbit.
   */
  processAddress = (e) => {
    // Show the loader.
    showFullScreenLoader();

    // If logged in user.
    if (drupalSettings.user.uid > 0) {
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
      isExpressDeliveryAvailable,
    } = this.props;

    let defaultVal = null;
    // If logged in user.
    if (drupalSettings.user.uid > 0) {
      const { fname, lname, mobile } = drupalSettings.user_name;
      defaultVal = {
        static: {
          fullname: makeFullName(fname, lname),
          telephone: mobile,
        },
      };
    } else if (cartVal.shipping.method !== null && cartVal.shipping.address !== null) {
      // If carrier info set, means shipping is set.
      // Get name info from there.
      const shippingAddress = cartVal.shipping.address;
      defaultVal = {
        static: {
          fullname: makeFullName(shippingAddress.firstname || '', shippingAddress.lastname || ''),
          email: shippingAddress.email,
          telephone: shippingAddress.telephone,
        },
      };
    }

    const areaSelected = getDeliveryAreaStorage();
    if (isExpressDeliveryEnabled() && areaSelected !== null) {
      const defaultArea = {};
      Object.entries(drupalSettings.address_fields).forEach(([key, val]) => {
        if (key === 'administrative_area') {
          defaultArea[val.key] = areaSelected.value[val.key];
        } else if (key === 'area_parent') {
          // Handling for parent area.
          defaultArea[val.key] = areaSelected.value[val.key];
        }
      });
      defaultVal = defaultArea;
    }

    const popupClassName = customerHasAddress(mainCart)
      ? getAddressPopupClassName()
      : 'spc-address-form-guest';

    return (
      <>
        {isMobile() && deliveryType !== 'click_and_collect' ? (
          <div className="address-form-mobile-only">
            <React.Suspense fallback={<Loading />}>
              <AddressContent
                cart={mainCart}
                processAddress={this.processAddress}
                type="shipping"
                showEmail={drupalSettings.user.uid === 0}
                default_val={defaultVal}
                isExpressDeliveryAvailable={isExpressDeliveryAvailable}
                isEmbeddedForm
              />
            </React.Suspense>
          </div>
        ) : (
          <WithModal modalStatusKey="deliveryType">
            {({ triggerOpenModal, triggerCloseModal, isModalOpen }) => (
              <div className="spc-empty-delivery-information">
                <div onClick={() => this.openModal(triggerOpenModal)} className="spc-checkout-empty-delivery-text">
                  <span>
                    {deliveryType === 'click_and_collect'
                      ? getCncSectionDescription()
                      : Drupal.t('please add your contact details and address.')}
                  </span>
                </div>
                <Popup
                  open={isModalOpen}
                  className={deliveryType === 'click_and_collect' ? '' : popupClassName}
                  closeOnEscape={false}
                  closeOnDocumentClick={false}
                >
                  {deliveryType === 'click_and_collect'
                    ? (
                      <ClickCollectContainer
                        closeModal={() => this.closeModal(triggerCloseModal)}
                      />
                    )
                    : (
                      <React.Suspense fallback={<Loading />}>
                        <AddressContent
                          closeModal={() => this.closeModal(triggerCloseModal)}
                          cart={mainCart}
                          showEditButton
                          headingText={Drupal.t('delivery information')}
                          processAddress={this.processAddress}
                          type="shipping"
                          showEmail={drupalSettings.user.uid === 0}
                          default_val={defaultVal}
                          isExpressDeliveryAvailable={isExpressDeliveryAvailable}
                          isEmbeddedForm={false}
                        />
                      </React.Suspense>
                    )}
                </Popup>
              </div>
            )}
          </WithModal>
        )}
      </>
    );
  }
}
