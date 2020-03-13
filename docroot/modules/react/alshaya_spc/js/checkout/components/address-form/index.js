import React from 'react';
import GoogleMap from '../../../utilities/map/GoogleMap';
import {
  createMarker,
  getMap,
  removeAllMarkersFromMap,
  fillValueInAddressFromGeocode,
} from '../../../utilities/map/map_utils';
import {
  getAreasList,
} from '../../../utilities/address_util';
import SectionTitle from '../../../utilities/section-title';
import DynamicFormField from '../dynamic-form-field';
import FixedFields from '../fixed-fields';

export default class AddressForm extends React.Component {
  _isMounted = true;

  constructor(props) {
    super(props);
    this.state = {
      area_list: null,
      cityChanged: false,
    };
  }

  // Submit handler for form.
  handleSubmit = (e) => {
    e.preventDefault();
    this.props.processAddress(e);
  };

  componentDidMount() {
    this._isMounted = true;
    // Listen to the map click event.
    document.addEventListener('mapClicked', this.eventListener, false);
  }

  componentWillUnmount() {
    this._isMounted = false;
    document.removeEventListener('mapClicked', this.eventListener, false);
  }

  eventListener = (e) => {
    const coords = e.detail.coords();
    if (this._isMounted) {
      this.positionMapAndUpdateAddress(coords);
    }
  };

  /**
   * Refresh the child areas list on selection / change
   * of the parent area.
   */
  refreshAreas = (parent_id, city_changed) => {
    this.setState({
      area_list: getAreasList(false, parent_id),
      cityChanged: city_changed,
    });
  };

  /**
   * Fills the address form with the geocode info and pan map.
   */
  positionMapAndUpdateAddress = (coords) => {
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
              // @Todo: Add some indication to user.
              console.log('Not available in the user country');
              return;
            }

            // Fill the info in address form.
            fillValueInAddressFromGeocode(address);
            // Remove all markers from the map.
            removeAllMarkersFromMap();
            // Pan the map to location.
            const marker = createMarker(coords, getMap());
            getMap().panTo(marker.getPosition());
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
    if (navigator && navigator.geolocation) {
      navigator.geolocation.getCurrentPosition((pos) => {
        const currentCoords = {
          lat: pos.coords.latitude,
          lng: pos.coords.longitude,
        };

        this.positionMapAndUpdateAddress(currentCoords);
      });
    }
  };

  render() {
    const dynamicFields = [];
    let default_val = [];
    if (this.props.default_val) {
      default_val = this.props.default_val;
    }

    let isEditAddress = false;
    // If address has area value set on load, means
    // we are editing address.
    if (default_val !== null
      && default_val.area !== undefined) {
      isEditAddress = true;
    }

    Object.entries(window.drupalSettings.address_fields).forEach(
      ([key, field]) => {
        dynamicFields.push(
          <DynamicFormField
            key={key}
            default_val={default_val}
            areasUpdate={this.refreshAreas}
            area_list={this.state.area_list}
            cityChanged={this.state.cityChanged}
            field_key={key}
            field={field}
          />,
        );
      },
    );

    const headingText = (this.props.headingText !== undefined)
      ? this.props.headingText
      : Drupal.t('delivery information');

    return (
      <div className="spc-address-form">
        {window.innerWidth > 768 && (
          <div className="spc-address-form-map">
            <GoogleMap isEditAddress={isEditAddress} />
          </div>
        )}
        <div className="spc-address-form-sidebar">
          <SectionTitle>{headingText}</SectionTitle>
          <a className="close" onClick={this.props.closeModal}>
            &times;
          </a>
          <div className="spc-address-form-wrapper">
            <div
              className="spc-deliver-button"
              onClick={() => this.deliverToCurrentLocation()}
            >
              {Drupal.t('deliver to my location')}
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
                  showEmail={this.props.showEmail}
                  default_val={default_val}
                />
                <div className="spc-address-form-actions" id="address-form-action">
                  <button
                    id="save-address"
                    className="spc-address-form-submit"
                    type="submit"
                  >
                    {Drupal.t('Save')}
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
