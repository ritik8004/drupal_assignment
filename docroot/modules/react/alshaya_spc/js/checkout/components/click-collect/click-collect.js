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
import FullScreenSVG from "../full-screen-svg";

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
      openSelectedStore: this.props.openSelectedStore || false,
      mapFullScreen: false
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

    // Show "select this store" button, if a store is selected.
    if (this.context.selectedStore && this.state.openSelectedStore === false) {
      this.selectStoreButtonVisibility(true);
    }

    document.addEventListener('markerClick', this.mapMarkerClick);
  }

  componentWillUnmount() {
    document.removeEventListener('markerClick', this.mapMarkerClick);
  }

  mapMarkerClick = e => {
    let index = e.detail.markerSettings.zIndex - 1;
    let allStores = this.cncListView.current.querySelectorAll('.select-store');
    [].forEach.call(allStores, function(el) {
      el.classList.remove("selected");
    });
    this.cncListView.current.querySelector('[data-index="' + index +'"]').classList.add('selected');
    if (window.innerWidth < 768 && this.cncMapView.current.style.display == "block") {
      this.toggleFullScreen(true);
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
    this.selectStoreButtonVisibility(false);
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
      let selectedStore = this.mapStoreList.current.querySelector('.selected');
      if (!selectedStore) {
        this.selectStoreButtonVisibility(false);
      }
      else {
        this.toggleFullScreen(true);
        this._openGivenMarkerIndex(selectedStore.dataset.storeCode);
      }
    } else {
      this.cncMapView.current.style.display = "none";
      this.cncListView.current.style.display = "block";
      let selectedStore = this.cncListView.current.querySelector('.selected');
      this.selectStoreButtonVisibility(!selectedStore ? false : true);
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
    this._openGivenMarkerIndex(this.context.selectedStore.code, storeList);
  };

  _openGivenMarkerIndex = (storeCode, storeList = this.context.storeList) => {
    let index = _findIndex(storeList, {
      code: storeCode
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

  closeSelectedStorePanel = () => {
    this.setState({
      openSelectedStore: false,
    });
    this.selectStoreButtonVisibility(true);
  };

  finalizeCurrentStore = (e) => {
    let selectedStore = this.cncListView.current.querySelector('.selected');
    this.finalizeStore(e, selectedStore.dataset.storeCode);
  };

  /**
   * Toggle map full screen.
   */
  toggleFullScreen = (fullscreen = null) => {
    if (fullscreen === true && document.fullscreenElement) {
      return;
    }

    if (document.fullscreenElement) {
      let self = this;
      this.setState({
        mapFullScreen: false
      });
      let selectedStore = this.mapStoreList.current.querySelector('.selected');
      if (selectedStore) {
        selectedStore.querySelector('.spc-map-list-close').click();
      }
      document.exitFullscreen()
        .then(() => {
          self.refreshMap();
        })
        .catch((err) => console.error(err));

      if (!selectedStore) {
        this.selectStoreButtonVisibility(false);
      }
    }
    else {
      this.cncMapView.current.requestFullscreen();
      this.setState({
        mapFullScreen: true
      });
    }
  };

  onMapStoreClose = (e) => {
    e.target.parentElement.parentElement.classList.remove('selected');
    this.selectStoreButtonVisibility(false);
    this.refreshMap();
  }

  selectStoreButtonVisibility = (action) => {
    const selectStoreBtn = document.getElementsByClassName('spc-cnc-store-actions')[0];
    if (action === true) {
      selectStoreBtn.classList.add('show');
    }
    else {
      selectStoreBtn.classList.remove('show');
    }
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
                    <button className='spc-cnc-full-screen' onClick={() => this.toggleFullScreen()}>
                      <FullScreenSVG mapFullScreen={this.state.mapFullScreen}/>
                    </button>
                    <div className="map-store-list" ref={this.mapStoreList}>
                      <StoreList
                        display="default"
                        store_list={storeList}
                        selected={this.context.selectedStore}
                        onStoreRadio={this.hightlightMapMarker}
                        onStoreFinalize={this.finalizeStore}
                        onStoreClose={this.onMapStoreClose}
                      />
                    </div>
                  </div>
                </DeviceView>
              </div>
            </div>
          </div>
          <ConditionalView condition={(storeList && storeList.length > 0)}>
            <div className="spc-cnc-store-actions">
              <button className="select-store" onClick={(e) => this.finalizeCurrentStore(e)}>
                {Drupal.t('select this store')}
              </button>
            </div>
          </ConditionalView>
          <SelectedStore
            store={selectedStore}
            open={openSelectedStore}
            closePanel={this.closeSelectedStorePanel}
          />
        </div>
      </div>
    );
  }
}

export default ClickCollect;
