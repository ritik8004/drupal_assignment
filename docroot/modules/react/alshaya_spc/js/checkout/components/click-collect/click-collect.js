import React from "react";
import _find from "lodash/find";
import _findIndex from "lodash/findIndex";
import { ClicknCollectContext } from "../../../context/ClicknCollect";
import { createFetcher } from "../../../utilities/api/fetcher";
import { fetchClicknCollectStores } from "../../../utilities/api/requests";
import {
  getDefaultMapCenter,
  getLocationAccess,
  removeFullScreenLoader,
  showFullScreenLoader
} from "../../../utilities/checkout_util";
import Loading from "../../../utilities/loading";
import SectionTitle from "../../../utilities/section-title";
import SelectedStore from "../selected-store";
import StoreList from "../store-list";
import ClicknCollectMap from "./ClicknCollectMap";

class ClickCollect extends React.Component {
  static contextType = ClicknCollectContext;

  constructor(props) {
    super(props);
    this.searchplaceInput = React.createRef();
    this.cncListView = React.createRef();
    this.cncMapView = React.createRef();
    this.nearMeBtn = React.createRef();
    this.autocomplete = null;
    this.state = {
      openSelectedStore: this.props.openSelectedStore || false
    };
  }

  componentDidMount() {
    // For autocomplete text field.
    if (!this.autocomplete && this.searchplaceInput) {
      this.autocomplete = new window.google.maps.places.Autocomplete(
        this.searchplaceInput.current,
        {
          types: [],
          componentRestrictions: { country: window.drupalSettings.country_code }
        }
      );

      this.autocomplete.addListener(
        "place_changed",
        this.placesAutocompleteHandler
      );
    }

    // Ask for location access when we don't have any coords.
    if (this.context.coords !== null && this.state.openSelectedStore) {
      this.showOpenMarker(this.context.storeList);
    }
  }

  /**
   * Autocomplete handler for the places list.
   */
  placesAutocompleteHandler = () => {
    const place = this.autocomplete.getPlace();
    this.nearMeBtn.current.classList.remove("active");
    if (typeof place !== "undefined" && typeof place.geometry !== "undefined") {
      this.fetchAvailableStores({
        lat: place.geometry.location.lat(),
        lng: place.geometry.location.lng()
      });
    }
  };

  /**
   * Get current location coordinates.
   */
  getCurrentPosition = e => {
    if (e) {
      e.preventDefault();
    }

    this.searchplaceInput.current.value = "";
    this.nearMeBtn.current.classList.add("active");
    getLocationAccess()
      .then(
        pos => {
          this.fetchAvailableStores({
            lat: pos.coords.latitude,
            lng: pos.coords.longitude
          });
        },
        reject => {
          this.nearMeBtn.current.classList.remove("active");
          this.fetchAvailableStores(getDefaultMapCenter());
        }
      )
      .catch(error => {
        console.log(error);
      });
    return false;
  };

  /**
   * Fetch available stores for given lat and lng.
   */
  fetchAvailableStores = coords => {
    showFullScreenLoader();
    // Create fetcher object to fetch stores.
    const storeFetcher = createFetcher(fetchClicknCollectStores);
    // Make api request.
    let list = storeFetcher.read(coords);
    list.then(response => {
      if (typeof response.error === "undefined") {
        this.context.updateCoordsAndStoreList(coords, response);
        if (this.state.openSelectedStore) {
          this.showOpenMarker(response);
        }
      }
      removeFullScreenLoader();
    });
  };

  selectStore = (e, store_code) => {
    e.preventDefault();
    // Find the store object with the given store-code from the store list.
    let store = _find(this.context.storeList, { code: store_code });
    this.context.updateSelectStore(store);
    this.setState({
      openSelectedStore: true
    });
  };

  toggleStoreView = (e, activeView) => {
    e.preventDefault();
    e.target.parentNode.childNodes.forEach(btn =>
      btn.classList.remove("active")
    );
    e.target.classList.add("active");
    if (activeView === "map") {
      this.cncMapView.current.style.display = "block";
      this.cncListView.current.style.display = "none";
      this.refreshMap();
    } else {
      this.cncMapView.current.style.display = "none";
      this.cncListView.current.style.display = "block";
    }
    return false;
  };

  // View selected store on map.
  storeViewOnMapSelected = makerIndex => {
    let map = window.spcMap;
    // Make the marker by default open.
    google.maps.event.trigger(map.map.mapMarkers[makerIndex], "click");
  };

  showOpenMarker = storeList => {
    if (!this.context.selectedStore) {
      return;
    }

    let index = _findIndex(storeList, {
      code: this.context.selectedStore.code
    });
    this.storeViewOnMapSelected(index);
  };

  refreshMap = () => {
    let map = window.spcMap;
    // Adjust the map, when we trigger the map view.
    google.maps.event.trigger(map.googleMap, "resize");
    // Auto zoom.
    map.googleMap.fitBounds(map.googleMap.bounds);
    // Auto center.
    map.googleMap.panToBounds(map.googleMap.bounds);
  };

  closePanel = () => {
    this.setState({
      openSelectedStore: false
    });
  };

  render() {
    let { coords, storeList, selectedStore } = this.context;
    let { openSelectedStore } = this.state;

    if (window.fetchStore == "pending") {
      return <Loading />;
    }

    let mapView = <ClicknCollectMap coords={coords} markers={storeList} />;

    return (
      <div className="spc-address-form">
        {window.innerWidth > 768 && (
          <div className="spc-address-form-map">{mapView}</div>
        )}
        <div className="spc-cnc-address-form-sidebar">
          <div
            className="spc-cnc-stores-list-map"
            style={{ display: openSelectedStore ? "none" : "block" }}
          >
            <SectionTitle>{Drupal.t("Collection Store")}</SectionTitle>
            <a className="close" onClick={this.props.closeModal}>
              &times;
            </a>
            <div className="spc-cnc-address-form-wrapper">
              <div className="spc-cnc-address-form-content">
                <SectionTitle>
                  {Drupal.t("find your nearest store")}
                </SectionTitle>
                <div className="spc-cnc-location-search-wrapper">
                  <div className="spc-cnc-store-search-form-item">
                    <input
                      ref={this.searchplaceInput}
                      className="form-search"
                      type="search"
                      id="edit-store-location"
                      name="store_location"
                      placeholder={drupalSettings.map.placeholder}
                      autoComplete="off"
                    />
                  </div>
                  <button
                    className="cc-near-me"
                    id="edit-near-me"
                    ref={this.nearMeBtn}
                    onClick={e => this.getCurrentPosition(e)}
                  >
                    {Drupal.t("Near me")}
                  </button>
                </div>
                {window.innerWidth < 768 && (
                  <div className="toggle-store-view">
                    <div className="toggle-buttons-wrapper">
                      <button
                        className="stores-list-view active"
                        onClick={e => this.toggleStoreView(e, "list")}
                      >
                        {Drupal.t("List view")}
                      </button>
                      <button
                        className="stores-map-view"
                        onClick={e => this.toggleStoreView(e, "map")}
                      >
                        {Drupal.t("Map view")}
                      </button>
                    </div>
                  </div>
                )}
                <div id="click-and-collect-list-view" ref={this.cncListView}>
                  <StoreList
                    store_list={storeList}
                    onStoreClick={this.storeViewOnMapSelected}
                    onSelectStore={this.selectStore}
                    selected={this.context.selectedStore}
                  />
                </div>
                {window.innerWidth < 768 && (
                  <div
                    className="click-and-collect-map-view"
                    style={{ display: "none" }}
                    ref={this.cncMapView}
                  >
                    {mapView}
                  </div>
                )}
              </div>
            </div>
          </div>
          <SelectedStore
            store={selectedStore}
            open={openSelectedStore}
            closePanel={this.closePanel}
          />
        </div>
      </div>
    );
  }
}

export default ClickCollect;
