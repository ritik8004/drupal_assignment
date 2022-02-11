import React from 'react';
import Popup from 'reactjs-popup';
import ConditionalView from '../../../common/components/conditional-view';
import {
  gerAreaLabelById, getAreaParentId, getUserAddressList, updateSelectedAddress,
} from '../../../utilities/address_util';
import { showFullScreenLoader } from '../../../utilities/checkout_util';
import {
  getAreaFieldKey, getDeliveryAreaStorage, setDeliveryAreaStorage,
} from '../../../utilities/delivery_area_util';
import dispatchCustomEvent from '../../../utilities/events';
import getStringMessage from '../../../utilities/strings';

export default class AreaConfirmationPopup extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      open: false,
      lastOrderArea: null,
      areaSelected: getDeliveryAreaStorage(),
    };
  }

  componentDidMount() {
    const { areaSelected } = this.state;
    const { cart, isExpressDeliveryAvailable } = this.props;
    const { address } = cart.cart.shipping;
    if (isExpressDeliveryAvailable && address) {
      const areaFieldKey = getAreaFieldKey();
      if (areaSelected !== null && areaFieldKey !== null) {
        if (areaSelected.value[areaFieldKey] !== parseInt(address[areaFieldKey], 10)) {
          this.setState({
            open: true,
            lastOrderArea: parseInt(address[areaFieldKey], 10),
          });
        }
      }
    }

    document.addEventListener('openAreaPopupConfirmation', this.openAreaPopupConfirmation);

    document.addEventListener('refreshAreaConfirmationState', this.refreshAreaConfirmationState);
  }

  closeModal = () => {
    document.body.classList.remove('open-form-modal');

    this.setState({
      open: false,
    });
  };

  /**
   * Update delivery area storage with last order address.
   */
  updateDeliveryAreaStorage = (e) => {
    e.preventDefault();
    const { lastOrderArea } = this.state;
    const areaParentInputValue = getAreaParentId(false, lastOrderArea);
    // Save the updated area/city in localStorage.
    const updatedArea = {
      label: gerAreaLabelById(false, lastOrderArea),
      area: parseInt(lastOrderArea, 10),
      governate: parseInt(areaParentInputValue[0].id, 10),
    };
    setDeliveryAreaStorage(updatedArea);
    this.closeModal();
  }

  /**
   * Update user address as per delivery area choosen by user.
   */
  updateDeliveryArea = (e) => {
    e.preventDefault();
    const { open, areaSelected } = this.state;
    let areaFound = false;
    const areaFieldKey = getAreaFieldKey();
    if (areaSelected !== null && areaFieldKey !== null) {
      if (drupalSettings.user.uid === 0) {
        dispatchCustomEvent('openAddressContentPopup', open);
      } else if (drupalSettings.user.uid > 0) {
        const addressList = getUserAddressList();
        if (addressList instanceof Promise) {
          addressList.then((list) => {
            const areaCheck = list.find((address) => {
              // If area already exists in address lists.
              // Make that address as default address of customer.
              if (areaSelected.value[areaFieldKey] === parseInt(address.administrative_area, 10)) {
                // Show loader.
                showFullScreenLoader();
                updateSelectedAddress(address, 'shipping');
                areaFound = true;
              }
              return areaCheck;
            });
            if (!areaFound) {
              dispatchCustomEvent('openAddressContentPopup', open);
            }
          });
        }
      }
    }
    this.closeModal();
  }

  /**
   * Opens delivery area pop up confirmation box.
   */
  openAreaPopupConfirmation = (e) => {
    const { cart } = this.props;
    if (e.detail) {
      this.refreshPopupState(cart);
    }
  }

  /**
   * Checks if area confirmation still not answered by customer.
   */
  refreshAreaConfirmationState = (e) => {
    if (e.detail) {
      this.refreshPopupState(e.detail);
    }
  }

  /**
   * Checks if address matches with storage value.
   * Refresh pop up state accordingly.
   */
  refreshPopupState = (cartData) => {
    const { areaSelected } = this.state;
    const { address } = cartData.cart.shipping;
    const areaFieldKey = getAreaFieldKey();
    if (areaSelected !== null && areaFieldKey !== null) {
      if (areaSelected.value[areaFieldKey] !== parseInt(address[areaFieldKey], 10)) {
        this.setState({
          open: true,
          lastOrderArea: parseInt(address[areaFieldKey], 10),
        });
      }
    }
  }

  render() {
    const { open, lastOrderArea, areaSelected } = this.state;
    const currentAreaLabel = gerAreaLabelById(false, lastOrderArea);
    const areaFieldKey = getAreaFieldKey();
    let storageAreaLabel = '';
    if (areaSelected !== null) {
      storageAreaLabel = gerAreaLabelById(false, areaSelected.value[areaFieldKey]);
    }
    return (
      <>
        <ConditionalView
          condition={storageAreaLabel !== '' && lastOrderArea !== null}
        >
          <div className="fadeInUp" style={{ animationDelay: '0.45s' }}>
            <Popup
              open={open}
              className="delivery-area-popup"
              closeOnDocumentClick={false}
              closeOnEscape={false}
            >
              <div className="delivery-area-block">
                <div className="delivery-area-question">
                  {getStringMessage('delivery_area_question', { '@currentAreaLabel': currentAreaLabel, '@storageAreaLabel': storageAreaLabel })}
                </div>
                <div className="delivery-area-options">
                  <button
                    className="delivery-area-yes"
                    id="delivery-area-yes"
                    type="button"
                    onClick={(e) => this.updateDeliveryArea(e)}
                  >
                    {Drupal.t('Yes')}
                  </button>
                  <button
                    className="delivery-area-no"
                    id="delivery-area-no"
                    type="button"
                    onClick={(e) => this.updateDeliveryAreaStorage(e)}
                  >
                    {Drupal.t('No')}
                  </button>
                </div>
              </div>
            </Popup>
          </div>
        </ConditionalView>
      </>
    );
  }
}
