import React from 'react';
import parse from 'html-react-parser';
import GoogleMap from '../../../utilities/map/GoogleMap';
import {
  createMarker,
  getMap,
  getHDMapZoom,
  removeAllMarkersFromMap,
  fillValueInAddressFromGeocode,
} from '../../../utilities/map/map_utils';
import {
  getAreasList,
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

export default class AddressForm extends React.Component {
  isComponentMounted = true;

  constructor(props) {
    super(props);
    this.state = {
      area_list: null,
      cityChanged: false,
      errorSuccessMessage: null,
      messageType: null,
      animateClass: '',
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
    const coords = e.detail.coords();
    if (this.isComponentMounted) {
      this.positionMapAndUpdateAddress(coords, false);
    }
  };

  /**
   * Show error on popup.
   */
  handleAddressPopUpError = (e) => {
    const { type, message } = e.detail;
    this.setState({
      messageType: type,
      errorSuccessMessage: message,
    });
    // Scroll to error.
    smoothScrollTo('.spc-address-form-sidebar .spc-checkout-section-title');
  };

  hidePopUpError = () => {
    this.setState({
      animateClass: 'fadeOutUp',
    });

    setTimeout(() => {
      this.setState({
        messageType: null,
        errorSuccessMessage: null,
      });
    }, 450);
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
  positionMapAndUpdateAddress = (coords, triggerEvent) => {
    const geocoder = new window.google.maps.Geocoder();
    geocoder.geocode(
      {
        location: coords,
      },
      (results, status) => {
        if (status === 'OK') {
          if (results[0]) {
            // Use this address info.
            const address = results[0].address_components;

            // Flag to determine if user country same as site.
            let userCountrySame = false;
            // Checking if user current location belongs to same
            // country or not by location coords geocode.
            for (let i = 0; i < address.length; i++) {
              if (address[i].types.indexOf('country') !== -1
                && address[i].short_name === drupalSettings.country_code) {
                userCountrySame = true;
                break;
              }
            }

            // If user and site country not same, don;t process.
            if (!userCountrySame) {
              if (triggerEvent) {
                removeFullScreenLoader();
                // Trigger event to update.
                dispatchCustomEvent('addressPopUpError', {
                  type: 'warning',
                  message: parse(getStringMessage('location_outside_country_hd')),
                });
              }
              return;
            }

            // Fill the info in address form.
            fillValueInAddressFromGeocode(address);
            // Remove all markers from the map.
            removeAllMarkersFromMap();
            // Pan the map to location.
            const marker = createMarker(coords, getMap());
            getMap().panTo(marker.getPosition());
            getMap().setZoom(getHDMapZoom());
            window.spcMarkers.push(marker);
          }
        }
      },
    );
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
    });
  }

  render() {
    const dynamicFields = [];
    const {
      default_val: defaultVal,
      headingText,
      closeModal,
      showEmail,
      formContext,
    } = this.props;
    const {
      area_list: areaList,
      cityChanged,
      errorSuccessMessage,
      messageType,
      animateClass,
    } = this.state;
    let defaultAddressVal = [];
    if (defaultVal) {
      defaultAddressVal = defaultVal;
    }

    // Check if billing address form.
    let mapToAddressFormBtnText = getStringMessage('hd_deliver_to_my_location');
    if (formContext === 'billing') {
      mapToAddressFormBtnText = getStringMessage('billing_select_my_location');
    }

    let isEditAddress = false;
    // If address has area value set on load, means
    // we are editing address.
    if (defaultAddressVal !== null
      && defaultAddressVal.area !== undefined) {
      isEditAddress = true;
    }

    Object.entries(window.drupalSettings.address_fields).forEach(
      ([key, field]) => {
        dynamicFields.push(
          <DynamicFormField
            key={key}
            default_val={defaultAddressVal}
            areasUpdate={this.refreshAreas}
            area_list={areaList}
            cityChanged={cityChanged}
            field_key={key}
            field={field}
          />,
        );
      },
    );

    const headingDeliveryText = (headingText !== undefined)
      ? headingText
      : getStringMessage('hd_delivery_information');

    return (
      <div className="spc-address-form">
        {window.innerWidth > 768 && (
          <div className="spc-address-form-map">
            <GoogleMap isEditAddress={isEditAddress} />
          </div>
        )}
        <div className="spc-address-form-sidebar">
          <SectionTitle>{headingDeliveryText}</SectionTitle>
          <a className="close" onClick={closeModal}>
            &times;
          </a>
          <div className="spc-address-form-wrapper">
            {errorSuccessMessage !== null
              && (
              <CheckoutMessage type={messageType} context={`new-address-form-modal modal ${animateClass}`}>
                {errorSuccessMessage}
                {messageType === 'warning'
                && (
                  <a href="#" onClick={() => this.hidePopUpError()}>{getStringMessage('dismiss')}</a>
                )}
              </CheckoutMessage>
              )}
            <div
              className="spc-deliver-button"
              onClick={() => this.deliverToCurrentLocation()}
            >
              {mapToAddressFormBtnText}
            </div>
            {window.innerWidth < 768 && (
              <div className="spc-address-form-map">
                <GoogleMap isEditAddress={isEditAddress} />
              </div>
            )}
            <div className="spc-address-form-content">
              <form
                className="spc-address-add"
                onSubmit={(e) => this.handleSubmit(e)}
              >
                <div className="delivery-address-fields">
                  {' '}
                  {dynamicFields}
                  {' '}
                </div>
                <FixedFields
                  showEmail={showEmail}
                  defaultVal={defaultAddressVal}
                />
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
