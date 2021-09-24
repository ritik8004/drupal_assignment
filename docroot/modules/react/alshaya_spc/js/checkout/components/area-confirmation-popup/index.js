import React from 'react';
import Popup from 'reactjs-popup';
import ConditionalView from '../../../common/components/conditional-view';
import {
  gerAreaLabelById, getAreaParentId, getUserAddressList, updateSelectedAddress,
} from '../../../utilities/address_util';
import { showFullScreenLoader } from '../../../utilities/checkout_util';
import { getDeliveryAreaStorage, setDeliveryAreaStorage } from '../../../utilities/delivery_area_util';
import dispatchCustomEvent from '../../../utilities/events';
import getStringMessage from '../../../utilities/strings';

const areaSelected = getDeliveryAreaStorage();
export default class AreaConfirmationPopup extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      open: false,
      lastOrderArea: null,
    };
  }

  componentDidMount() {
    const {
      cart: { cart: { shipping: { address } } },
    } = this.props;
    document.addEventListener('openAreaPopupConfirmation', this.openAreaPopupConfirmation);

    document.addEventListener('refreshAreaConfirmationState', this.refreshAreaConfirmationState);

    if (areaSelected !== null && drupalSettings.address_fields) {
      const areaFieldKey = drupalSettings.address_fields.administrative_area.key;
      if (areaSelected.value.area !== parseInt(address[areaFieldKey], 10)) {
        this.setState({
          open: true,
          lastOrderArea: parseInt(address[areaFieldKey], 10),
        });
      }
    }
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
    const { open } = this.state;
    let areaFound = false;
    if (drupalSettings.user.uid > 0 && areaSelected !== null) {
      const addressList = getUserAddressList();
      if (addressList instanceof Promise) {
        addressList.then((list) => {
          const areaCheck = list.find((address) => {
            // If area already exists in address lists.
            // Make that address as default address of customer.
            if (areaSelected.value.area === parseInt(address.administrative_area, 10)) {
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
    const { address } = cartData.cart.shipping;
    if (areaSelected !== null && drupalSettings.address_fields) {
      const areaFieldKey = drupalSettings.address_fields.administrative_area.key;
      if (areaSelected.value.area !== parseInt(address[areaFieldKey], 10)) {
        this.setState({
          open: true,
          lastOrderArea: parseInt(address[areaFieldKey], 10),
        });
      }
    }
  }

  render() {
    const { open, lastOrderArea } = this.state;
    const currentAreaLabel = gerAreaLabelById(false, lastOrderArea);
    let storageAreaLabel = '';
    if (areaSelected !== null) {
      storageAreaLabel = gerAreaLabelById(false, areaSelected.value.area);
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
