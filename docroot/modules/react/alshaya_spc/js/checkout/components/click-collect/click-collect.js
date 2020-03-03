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
import SelectedStore from "./components/SelectedStore";
import StoreList from "./components/StoreList";
import ClicknCollectMap from "./components/ClicknCollectMap";
import ToggleButton from "./components/ToggleButton";
import LocationSearchForm from "./components/LocationSearchForm";
import ConditionalView from "../../../common/components/conditional-view";
import DeviceView from "../../../common/components/device-view";

class ClickCollect extends React.Component {
  static contextType = ClicknCollectContext;

  constructor(props) {
    super(props);
    this.searchRef = React.createRef();
    this.cncListView = React.createRef();
    this.cncMapView = React.createRef();

    this.mapStoreList = React.createRef();
    this.autocomplete = null;
    this.searchplaceInput = null;
    this.nearMeBtn = null;
    this.state = {
      openSelectedStore: this.props.openSelectedStore || false
    };
  }

  componentDidMount() {
    // For autocomplete text field.
    if (!this.autocomplete && this.searchRef) {
      this.searchplaceInput = this.searchRef.current.getElementsByTagName('input')[0];
      this.autocomplete = new window.google.maps.places.Autocomplete(
        this.searchplaceInput,
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
    this.nearMeBtn = this.searchRef.current.getElementsByTagName('button')[0];

    // Ask for location access when we don't have any coords.
    if (this.context.coords !== null && this.state.openSelectedStore) {
      this.showOpenMarker(this.context.storeList);
    }
    document.addEventListener('mapTriggered', this.mapMarkerClick);
  }

  componentWillUnmount() {
    document.removeEventListener('mapTriggered', this.mapMarkerClick);
  }

  mapMarkerClick = e => {
    let index = e.detail.markerSettings.zIndex - 1;
    let allStores = this.cncListView.current.querySelectorAll('.select-store');
    [].forEach.call(allStores, function(el) {
      el.classList.remove("selected");
    });
    this.cncListView.current.querySelector('[data-index="' + index +'"]').classList.add('selected');
    if (window.innerWidth < 768 && this.cncMapView.current.style.display == "block") {
      this.toggleFullScreen();
      this.mapStoreList.current.querySelector('[data-index="' + index +'"]').classList.add('selected');
    }
  }

  /**
   * Autocomplete handler for the places list.
   */
  placesAutocompleteHandler = () => {
    const place = this.autocomplete.getPlace();
    this.nearMeBtn.classList.remove("active");
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

    this.searchplaceInput.value = "";
    this.nearMeBtn.classList.add("active");
    getLocationAccess()
      .then(
        pos => {
          this.fetchAvailableStores({
            lat: pos.coords.latitude,
            lng: pos.coords.longitude
          });
        },
        reject => {
          this.nearMeBtn.classList.remove("active");
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

  finalizeStore = (e, store_code) => {
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
  hightlightMapMarker = makerIndex => {
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

    // Wait for all markers to be placed in map before
    // Clicking on the marker.
    setTimeout(() => {
      this.hightlightMapMarker(index);
    }, 100);

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

  finalizeCurrentStore = (e) => {
    let selectedStore = this.cncListView.current.querySelector('.selected');
    this.finalizeStore(e, selectedStore.dataset.storeCode);
  }

  toggleFullScreen = () => {
    if (document.fullscreenElement) {
      let self = this;
      document.exitFullscreen()
        .then(() => {
          self.refreshMap();
        })
        .catch(err => console.error(err))
    } else {
      this.cncMapView.current.requestFullscreen();
    }
  }

  onStoreClose = () => {
    this.refreshMap();
  }

  render() {
    let { coords, storeList, selectedStore } = this.context;
    let { openSelectedStore } = this.state;
    let { closeModal } = this.props;

    if (window.fetchStore == "pending") {
      return <Loading />;
    }

    let mapView = <ClicknCollectMap coords={coords} markers={storeList} />;

    return (
      <div className="spc-address-form">
        <DeviceView device="above-mobile">
          <div className="spc-address-form-map">{mapView}</div>
        </DeviceView>
        <div className="spc-cnc-address-form-sidebar">
          <div
            className="spc-cnc-stores-list-map"
            style={{ display: openSelectedStore ? "none" : "block" }}
          >
            <SectionTitle>{Drupal.t("Collection Store")}</SectionTitle>
            <a className="close" onClick={closeModal}>
              &times;
            </a>
            <div className="spc-cnc-address-form-wrapper">
              <div className="spc-cnc-address-form-content">
                <SectionTitle>
                  {Drupal.t("find your nearest store")}
                </SectionTitle>
                <LocationSearchForm ref={this.searchRef} getCurrentPosition={this.getCurrentPosition}/>
                <DeviceView device="mobile">
                  <ToggleButton toggleStoreView={this.toggleStoreView}/>
                </DeviceView>
                <div id="click-and-collect-list-view" ref={this.cncListView}>
                  <StoreList
                    display="accordion"
                    store_list={storeList}
                    selected={this.context.selectedStore}
                    onStoreRadio={this.hightlightMapMarker}
                    onStoreFinalize={this.finalizeStore}
                  />
                </div>
                <DeviceView device="mobile">
                  <div
                    className="click-and-collect-map-view"
                    style={{ display: "none" }}
                    ref={this.cncMapView}
                  >
                    {mapView}
                    <button onClick={() => this.toggleFullScreen()}>Full screen</button>
                    <div className="map-store-list" ref={this.mapStoreList}>
                      <StoreList
                        display="default"
                        store_list={storeList}
                        selected={this.context.selectedStore}
                        onStoreRadio={this.hightlightMapMarker}
                        onStoreFinalize={this.finalizeStore}
                        onStoreClose={this.onStoreClose}
                      />
                    </div>
                  </div>
                </DeviceView>
              </div>
            </div>
            <ConditionalView condition={(storeList && storeList.length > 0)}>
              <div className="store-actions">
                <button className="select-store" onClick={e => this.finalizeCurrentStore(e)}>
                  {Drupal.t('select this store')}
                </button>
              </div>
            </ConditionalView>
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
