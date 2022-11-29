import React from 'react';
import parse from 'html-react-parser';
import {
  createMarker,
  getMap,
  getHDMapZoom,
  removeAllMarkersFromMap,
  fillValueInAddressFromGeocode,
  getUserLocation,
} from '../../../utilities/map/map_utils';
import {
  getAreasList,
  errorOnDropDownFieldsNotFilled,
} from '../../../utilities/address_util';
import SectionTitle from '../../../utilities/section-title';
import DynamicFormField from '../dynamic-form-field';
import FixedFields from '../fixed-fields';
import CheckoutMessage from '../../../utilities/checkout-message';
import { smoothScrollTo } from '../../../utilities/smoothScroll';
import getStringMessage from '../../../utilities/strings';
import dispatchCustomEvent from '../../../utilities/events';
import {
  showFullScreenLoader,
  removeFullScreenLoader,
} from '../../../utilities/checkout_util';
import { isExpressDeliveryEnabled } from '../../../../../js/utilities/expressDeliveryHelper';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

export default class AddressForm extends React.Component {
  isComponentMounted = true;

  constructor(props) {
    super(props);
    this.state = {
      area_list: null,
      cityChanged: false,
      errorSuccessMessage: null,
      messageType: null,
      dismissButton: true,
    };
  }

  componentDidMount() {
    this.isComponentMounted = true;
    // Listen to the map click event.
    document.addEventListener('mapClicked', this.eventListener, false);
    document.addEventListener('addressPopUpError', this.handleAddressPopUpError, false);
  }

  componentWillUnmount() {
    this.isComponentMounted = false;
    document.removeEventListener('mapClicked', this.eventListener, false);
    document.removeEventListener('addressPopUpError', this.handleAddressPopUpError, false);
  }

  // Submit handler for form.
  handleSubmit = (e) => {
    const { processAddress } = this.props;
    e.preventDefault();
    processAddress(e);
  };

  eventListener = (e) => {
    if (!this.isComponentMounted) {
      return;
    }
    const coords = e.detail.coords();
    this.positionMapAndUpdateAddress(coords, false);
  };

  /**
   * Show error on popup.
   */
  handleAddressPopUpError = (e) => {
    if (!this.isComponentMounted) {
      return;
    }
    const { type, message, showDismissButton } = e.detail;
    this.setState({
      messageType: type,
      errorSuccessMessage: message,
      dismissButton: showDismissButton,
    });
    const { isEmbeddedForm } = this.props;
    let errorClass = '.spc-address-form-sidebar';
    if (!hasValue(isEmbeddedForm)) {
      errorClass += ' .spc-checkout-section-title';
    }
    // Scroll to error.
    smoothScrollTo(errorClass);
  };

  hidePopUpError = (e) => {
    e.target.parentNode.parentNode.classList.add('fadeOutUp');
    // Wait for warning message fade out animation.
    setTimeout(() => {
      this.setState({
        messageType: null,
        errorSuccessMessage: null,
        dismissButton: false,
      });
    }, 200);
  };

  /**
   * Refresh the child areas list on selection / change
   * of the parent area.
   */
  refreshAreas = (parentId, cityChanged) => {
    this.setState({
      area_list: getAreasList(false, parentId),
      cityChanged,
    });
  };

  /**
   * Fills the address form with the geocode info and pan map.
   */
  positionMapAndUpdateAddress = async (coords, triggerEvent) => {
    try {
      const [userCountrySame, addresses] = await getUserLocation(coords);
      // If user and site country not same, don;t process.
      if (!userCountrySame) {
        if (triggerEvent) {
          removeFullScreenLoader();
          // Trigger event to update.
          dispatchCustomEvent('addressPopUpError', {
            type: 'warning',
            message: parse(getStringMessage('location_outside_country_hd')),
            showDismissButton: true,
          });
        }
        return;
      }

      // Fill the info in address form.
      fillValueInAddressFromGeocode(addresses);
      // Remove all markers from the map.
      removeAllMarkersFromMap();
      // Pan the map to location.
      const marker = createMarker(coords, getMap());
      getMap().panTo(marker.getPosition());
      getMap().setZoom(getHDMapZoom());
      window.spcMarkers.push(marker);
      // Check if area/city filled or not to show
      // error and scroll for user.
      errorOnDropDownFieldsNotFilled();
      removeFullScreenLoader();
    } catch (error) {
      Drupal.logJavascriptError('homedelivery-checkUserCountry', error, GTM_CONSTANTS.CHECKOUT_ERRORS);
    }
  };

  /**
   * When user click on deliver to current location.
   */
  deliverToCurrentLocation = () => {
    // Show loader.
    showFullScreenLoader();
    if (navigator && navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        this.locationSuccessCallback,
        this.locationErrorCallback,
      );
    }
  };

  /**
   * Success callback handler on location access.
   */
  locationSuccessCallback = (pos) => {
    const currentCoords = {
      lat: pos.coords.latitude,
      lng: pos.coords.longitude,
    };

    this.positionMapAndUpdateAddress(currentCoords, true);
  }

  /**
   * Error callback handler on location access.
   */
  locationErrorCallback = () => {
    // Remove loader.
    removeFullScreenLoader();
    // Show location access message.
    dispatchCustomEvent('addressPopUpError', {
      type: 'warning',
      message: getStringMessage('location_access_denied'),
      showDismissButton: true,
    });
  }

  render() {
    const dynamicFields = [];
    const {
      default_val: defaultVal,
      headingText,
      closeModal,
      showEmail,
      shippingAsBilling = null,
      isExpressDeliveryAvailable,
      // if fillDefaultValue is true,
      // Default value will be always be available to form.
      fillDefaultValue,
      enabledFieldsWithMessages,
      isEmbeddedForm,
    } = this.props;

    const {
      area_list: areaList,
      cityChanged,
      errorSuccessMessage,
      messageType,
      dismissButton,
    } = this.state;

    let defaultAddressVal = [];
    if (defaultVal) {
      if ((typeof fillDefaultValue !== 'undefined' && fillDefaultValue)
      || (isExpressDeliveryEnabled() && isExpressDeliveryAvailable)
      || !isExpressDeliveryEnabled()) {
        defaultAddressVal = defaultVal;
      }
    }

    Object.entries(window.drupalSettings.address_fields).forEach(
      ([key, field]) => {
        dynamicFields.push(
          <DynamicFormField
            key={key}
            default_val={shippingAsBilling ? '' : defaultAddressVal}
            areasUpdate={this.refreshAreas}
            area_list={areaList}
            cityChanged={cityChanged}
            field_key={key}
            field={field}
            // This prop is an object where object keys are field-names which will
            // be enabled in the form and values are default message on the field
            // example {mobile: Please update mobile number}
            enabledFieldsWithMessages={enabledFieldsWithMessages}
          />,
        );
      },
    );

    const headingDeliveryText = (headingText !== undefined)
      ? headingText
      : getStringMessage('hd_delivery_information');

    return (
      <div className="spc-address-form">
        <div className="spc-address-form-sidebar">
          {!hasValue(isEmbeddedForm)
          && (
            <>
              <SectionTitle>{headingDeliveryText}</SectionTitle>
              <a className="close" onClick={() => closeModal()}>
                &times;
              </a>
            </>
          )}
          <div className="spc-address-form-wrapper">
            {errorSuccessMessage !== null
              && (
              <CheckoutMessage type={messageType} context="new-address-form-modal modal">
                {errorSuccessMessage}
                {messageType === 'warning' && dismissButton === true
                && (
                  <button id="address-hide-error-button" type="button" onClick={(e) => this.hidePopUpError(e)}>
                    {getStringMessage('dismiss')}
                  </button>
                )}
              </CheckoutMessage>
              )}
            <div className="spc-address-form-content">
              <form
                className="spc-address-add"
                onSubmit={(e) => this.handleSubmit(e)}
              >
                <FixedFields
                  showEmail={showEmail}
                  defaultVal={defaultAddressVal}
                  type="hd"
                  // This prop is an object where object keys are field-names which will
                  // be enabled in the form and values are default message on the field
                  // example {mobile: Please update mobile number}
                  enabledFieldsWithMessages={enabledFieldsWithMessages}
                />
                <div className="delivery-address-fields">
                  {' '}
                  {dynamicFields}
                  {' '}
                </div>
                <div className="spc-address-form-actions" id="address-form-action">
                  <button
                    id="save-address"
                    className="spc-address-form-submit"
                    type="submit"
                  >
                    {getStringMessage('address_save')}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    );
  }
}
