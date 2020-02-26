import React from "react";
import GoogleMap from "../../../utilities/map/GoogleMap";
import {
  createMarker,
  getArea,
  getBlock,
  getMap,
  removeAllMarkersFromMap
} from "../../../utilities/map/map_utils";
import SectionTitle from "../../../utilities/section-title";
import DynamicFormField from "../dynamic-form-field";
import FixedFields from "../fixed-fields";

export default class AddressForm extends React.Component {
  _isMounted = true;
  constructor(props) {
    super(props);
    this.state = {
      area_list: null
    };
  }

  // Submit handler for form.
  handleSubmit = e => {
    e.preventDefault();
    this.props.processAddress(e);
  };

  componentDidMount() {
    this._isMounted = true;
    // Listen to the map click event.
    document.addEventListener("mapClicked", this.eventListener, false);
  }

  componentWillUnmount() {
    this._isMounted = false;
    document.removeEventListener("mapClicked", this.eventListener, false);
  }

  eventListener = e => {
    var coords = e.detail.coords();
    if (this._isMounted) {
      this.positionMapAndUpdateAddress(coords);
    }
  };

  /**
   * Refresh the child areas list on selection / change
   * of the parent area.
   */
  refreshAreas = area_list => {
    let data = new Array();
    Object.entries(area_list).forEach(([tid, tname]) => {
      data[tid] = {
        value: tid,
        label: tname
      };
    });

    this.setState({
      area_list: data
    });
  };

  /**
   * Fills the address form with the geocode info and pan map.
   */
  positionMapAndUpdateAddress = coords => {
    let geocoder = new window.google.maps.Geocoder();
    geocoder.geocode(
      {
        location: coords
      },
      function(results, status) {
        if (status === "OK") {
          if (results[0]) {
            // Use this address info.
            const address = results[0].address_components;
            let area = getArea(address);
            let block = getBlock(address);
            // Fill the address form.
            document.getElementById("address_line2").value = area;
            document.getElementById("locality").value = block;
            // Remove all markers from the map.
            removeAllMarkersFromMap();
            // Pan the map to location.
            let marker = createMarker(coords, getMap());
            getMap().panTo(marker.getPosition());
            window.spcMarkers.push(marker);
          }
        }
      }
    );
  };

  /**
   * When user click on deliver to current location.
   */
  deliverToCurrentLocation = () => {
    if (navigator && navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(pos => {
        let currentCoords = {
          lat: pos.coords.latitude,
          lng: pos.coords.longitude
        };

        this.positionMapAndUpdateAddress(currentCoords);
      });
    }
  };

  render() {
    let dynamicFields = [];
    let default_val = [];
    if (this.props.default_val) {
      default_val = this.props.default_val;
    }

    Object.entries(window.drupalSettings.address_fields).forEach(
      ([key, field]) => {
        dynamicFields.push(
          <DynamicFormField
            key={key}
            default_val={default_val}
            areasUpdate={this.refreshAreas}
            area_list={this.state.area_list}
            field_key={key}
            field={field}
          />
        );
      }
    );

    return (
      <div className="spc-address-form">
        {window.innerWidth > 768 && (
          <div className="spc-address-form-map">
            <GoogleMap />
          </div>
        )}
        <div className="spc-address-form-sidebar">
          <SectionTitle>{Drupal.t("Delivery information")}</SectionTitle>
          <div className="spc-address-form-wrapper">
            <div
              className="spc-deliver-button"
              onClick={() => this.deliverToCurrentLocation()}
            >
              {Drupal.t("Deliver to my location")}
            </div>
            {window.innerWidth < 768 && (
              <div className="spc-address-form-map">
                <GoogleMap />
              </div>
            )}
            <div className="spc-address-form-content">
              <form
                className="spc-address-add"
                onSubmit={e => this.handleSubmit(e)}
              >
                <div className="delivery-address-fields"> {dynamicFields} </div>
                {this.props.show_prefered && (
                  <div>
                    <input type="checkbox" name="prefered-address" />
                    {Drupal.t("Preferred address")}
                  </div>
                )}
                <FixedFields
                  showEmail={this.props.showEmail}
                  default_val={default_val}
                />
                <div className="spc-address-form-actions">
                  <button
                    id="save-address"
                    className="spc-address-form-submit"
                    type="submit"
                  >
                    {Drupal.t("Save")}
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
