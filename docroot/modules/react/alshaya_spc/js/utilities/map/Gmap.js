import isRTL from '../rtl';
import dispatchCustomEvent from '../events';
import { getDefaultMapCenter } from '../checkout_util';
import { hasValue } from '../../../../js/utilities/conditionsUtility';

export class Gmap {
  constructor() {
    this.map = {
      settings: {
        zoom: (typeof drupalSettings.map.center !== 'undefined' && ({}).hasOwnProperty.call(drupalSettings.map.center, 'zoom')) ? drupalSettings.map.center.zoom : 7,
        maxZoom: 18,
        zoomControl: true,
        fullscreenControl: false,
        mapTypeControl: false,
        scrollwheel: true,
        disableDoubleClickZoom: false,
        draggable: true,
        gestureHandling: 'auto',
        disableAutoPan: true,
        map_marker: {
          active: null,
          inActive: null,
        },
        streetViewControl: false,
        info_auto_display: false,
      },
      googleMap: null,
      geoCoder: new google.maps.Geocoder(),
      mapMarkers: [],
    };

    if (typeof drupalSettings.map !== 'undefined' && typeof drupalSettings.map.map_marker !== 'undefined') {
      this.map.settings.map_marker.active = drupalSettings.map.map_marker.active;
      this.map.settings.map_marker.inActive = drupalSettings.map.map_marker.in_active;
    }
  }

  initMap(container) {
    this.map.googleMap = new google.maps.Map(container, {
      zoom: this.map.settings.zoom,
      clickableIcons: false,
      maxZoom: this.map.settings.maxZoom,
      minZoom: this.map.settings.minZoom,
      fullscreenControl: this.map.settings.fullscreenControl,
      mapTypeId: google.maps.MapTypeId.ROADMAP,
      mapTypeControl: this.map.settings.mapTypeControl,
      mapTypeControlOptions: {
        position: isRTL() === true
          ? google.maps.ControlPosition.RIGHT_BOTTOM
          : google.maps.ControlPosition.LEFT_BOTTOM,
      },
      zoomControl: this.map.settings.zoomControl,
      zoomControlOptions: {
        style: google.maps.ZoomControlStyle.SMALL,
        position: isRTL() === true
          ? google.maps.ControlPosition.RIGHT_BOTTOM
          : google.maps.ControlPosition.LEFT_BOTTOM,
      },
      streetViewControl: this.map.settings.streetViewControl,
      streetViewControlOptions: {
        position: isRTL() === true
          ? google.maps.ControlPosition.RIGHT_CENTER
          : google.maps.ControlPosition.LEFT_CENTER,
      },
      scrollwheel: this.map.settings.scrollwheel,
      disableDoubleClickZoom: this.map.settings.disableDoubleClickZoom,
      draggable: this.map.settings.draggable,
      gestureHandling: this.map.settings.gestureHandling,
    });
  }

  /**
   * Get map object.
   */
  getMapObj = () => this.map.googleMap;

  /**
   * Set map object.
   */
  setCurrentMap = (mapObj) => {
    this.map.googleMap = mapObj;
  }

  /**
   * Set center of the map.
   */
  setCenter = (coords, callBackFunc = null) => {
    if (hasValue(coords)) {
      this.map.googleMap.setCenter(coords);
      this.map.googleMap.setZoom(this.map.settings.zoom);
      return;
    }

    const defaultLocation = getDefaultMapCenter();
    if (hasValue(defaultLocation)) {
      const { lat, lng } = defaultLocation;
      const position = new google.maps.LatLng(parseFloat(lat), parseFloat(lng));
      this.map.googleMap.setCenter(position);
      this.map.googleMap.setZoom(this.map.settings.zoom);
      return;
    }

    const { googlemap, settings: mapSettings } = this.map;
    this.map.geoCoder.geocode({
      componentRestrictions: {
        country: drupalSettings.country_code,
      },
    }, (results, status) => {
      if (status === google.maps.GeocoderStatus.OK) {
        // Just center the map and don't do anything.
        googlemap.setCenter(results[0].geometry.location);
        googlemap.setZoom(mapSettings.zoom);
        if (callBackFunc) {
          callBackFunc.call(results);
        }
      }
    });
  }

  /**
   * Initiate geocoder.
   */
  initGeoCoder = () => {
    this.map.geoCoder = new google.maps.Geocoder();
  }

  /**
   * Set marker for map.
   *
   * Pass second parameter true to not show infowindo and
   * false to add infowindow and it's close event.
   */
  setMapMarker = (markerSettings, showInfoWindow = false, mapIcon = '') => {
    this.map.mapMarkers = this.map.mapMarkers || [];
    const currentMarkerSettings = { ...markerSettings };

    const { active: markerActiveIcon, inActive: markerInActiveIcon } = this.map.settings.map_marker;
    const mapMarkerIcon = mapIcon || markerInActiveIcon;

    if (typeof mapMarkerIcon === 'string') {
      // Add the marker icon.
      currentMarkerSettings.icon = {
        url: mapMarkerIcon,
      };
    }

    if (!currentMarkerSettings.map) {
      currentMarkerSettings.map = this.map.googleMap;
    }

    // Add the marker to the map.
    /** @type {GoogleMarker} */
    const currentMarker = new google.maps.Marker(currentMarkerSettings);
    let currentInfoWindow = null;

    if (showInfoWindow === true) {
      // Set the info popup text.
      currentInfoWindow = new google.maps.InfoWindow({
        content: currentMarkerSettings.infoWindowContent,
        maxWidth: 209,
        disableAutoPan: this.map.settings.disableAutoPan,
      });
    }

    const { map } = this;
    let clickedMarker = '';
    currentMarker.addListener('click', () => {
      map.mapMarkers.forEach((tempMarker) => tempMarker.setIcon({
        url: mapIcon ? tempMarker.icon.url : currentMarkerSettings.icon.url,
      }));
      currentMarker.setIcon(
        { url: mapIcon || markerActiveIcon, scaledSize: new google.maps.Size(47, 66) },
      );
      clickedMarker = currentMarker;

      if (currentMarkerSettings.infoWindowSolitary && showInfoWindow === true) {
        if (typeof map.infoWindow !== 'undefined') {
          map.infoWindow.close();
        }
        if (currentInfoWindow) {
          map.infoWindow = currentInfoWindow;
          currentInfoWindow.open(map.googleMap, currentMarker);
        }
      }
      // Set the marker to center of the map on click.
      map.googleMap.setCenter(currentMarker.getPosition());
      map.googleMap.setZoom(12);

      dispatchCustomEvent('markerClick', {
        marker: currentMarker,
        markerSettings: currentMarkerSettings,
      });
    });

    google.maps.event.addListener(currentMarker, 'mouseover', () => {
      if (clickedMarker === currentMarker) {
        return;
      }
      currentMarker.setIcon(mapIcon || markerActiveIcon);
    });

    google.maps.event.addListener(currentMarker, 'mouseout', () => {
      if (clickedMarker === currentMarker) {
        return;
      }
      currentMarker.setIcon(currentMarkerSettings.icon);
    });

    if (showInfoWindow === true) {
      google.maps.event.addListener(currentInfoWindow, 'closeclick', () => {
        clickedMarker = '';
        currentMarker.setIcon(currentMarkerSettings.icon);
        // Auto zoom.
        map.googleMap.fitBounds(map.googleMap.bounds);
        // Auto center.
        map.googleMap.panToBounds(map.googleMap.bounds);
      });

      if (map.settings.info_auto_display) {
        google.maps.event.addListenerOnce(map.googleMap, 'tilesloaded', () => {
          google.maps.event.trigger(currentMarker, 'click');
        });
      }
    }

    currentMarker.infoWindow = currentInfoWindow;
    this.map.mapMarkers.push(currentMarker);
    return currentMarker;
  };

  resetIcon = (currentMarker, options = {}) => {
    const { inActive } = options.map_marker || this.map.settings.map_marker;
    currentMarker.setIcon(inActive);
  }

  highlightIcon = (currentMarker, options = {}) => {
    const { active } = options.map_marker || this.map.settings.map_marker;
    currentMarker.setIcon(active);
  }

  /**
   * Remove marker(s) from map.
   */
  removeMapMarker = (map = null) => {
    this.map = map !== null ? map : this.map;
    if (this.map.mapMarkers.length > 0) {
      this.map.mapMarkers.forEach((marker) => {
        marker.setMap();
      });
    }
    this.map.mapMarkers = [];
  }

  closeAllInfoWindow = (map = null) => {
    this.map = map !== null ? map : this.map;
    this.map.mapMarkers.forEach((marker) => {
      marker.infoWindow.close(this.map, marker);
    });
  }
}

// Helper function to create a global object of gamp
// to avoid creation of window.spcmap for cnc.
const globalGmap = (function globalGmapHelper() {
  let gmapObj;

  return {
    create() {
      if (!gmapObj) {
        gmapObj = new Gmap();
      }
      return gmapObj;
    },
  };
}());
export default globalGmap;
