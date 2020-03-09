import _isEmpty from 'lodash/isEmpty';
import { isRTL } from '../rtl';
import { dispatchCustomEvent } from '../events';
import { getDefaultMapCenter } from '../checkout_util';

export default class Gmap {
  constructor() {
    this.map = {
      settings: {
        zoom: (drupalSettings.map.center.length > 0 && ({}).hasOwnProperty.call(drupalSettings.map.center, 'zoom')) ? drupalSettings.map.center.zoom : 7,
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
          icon: null,
          label_position: null,
        },
        streetViewControl: false,
        info_auto_display: false,
      },
      googleMap: null,
      geoCoder: new google.maps.Geocoder(),
      mapMarkers: [],
    };

    if (typeof drupalSettings.map !== 'undefined' && typeof drupalSettings.map.map_marker !== 'undefined') {
      this.map.settings.map_marker.icon = drupalSettings.map.map_marker.icon;
      this.map.settings.map_marker.label_position = drupalSettings.map.map_marker.label_position;
    }
  }

  initMap(container) {
    this.map.googlemap = new google.maps.Map(container, {
      zoom: this.map.settings.zoom,
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

    return this.map.googlemap;
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
    if (!_isEmpty(coords)) {
      this.map.googleMap.setCenter(coords);
      this.map.googleMap.setZoom(this.map.settings.zoom);
      return;
    }

    const defaultLocation = getDefaultMapCenter();
    if (!_isEmpty(defaultLocation)) {
      const { latitude, longitude } = defaultLocation();
      const position = new google.maps.LatLng(parseFloat(latitude), parseFloat(longitude));
      this.map.googleMap.setCenter(position);
      this.map.googleMap.setZoom(this.map.settings.zoom);
      return;
    }

    this.map.geoCoder.geocode({
      componentRestrictions: {
        country: drupalSettings.country_code,
      },
    }, (results, status) => {
      if (status === google.maps.GeocoderStatus.OK) {
        // Just center the map and don't do anything.
        window.spcMap.googleMap.setCenter(results[0].geometry.location);
        window.spcMap.googleMap.setZoom(window.spcMap.map.settings.zoom);
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
  setMapMarker = (markerSettings, showInfoWindow = false) => {
    this.map.mapMarkers = this.map.mapMarkers || [];
    const currentMarkerSettings = { ...markerSettings };

    const { icon: markerIconPath, label_position: labelPosition } = this.map.settings.map_marker;

    if (typeof markerIconPath === 'string') {
      // Add the marker icon.
      currentMarkerSettings.icon = {
        url: markerIconPath,
        labelOrigin: new google.maps.Point(labelPosition.x, labelPosition.y),
        scaledSize: new google.maps.Size(31, 48),
      };

      // If only single digit move them closer to the center.
      if (drupalSettings.path.currentLanguage === 'ar' && currentMarkerSettings.label.length === 1) {
        currentMarkerSettings.icon.labelOrigin = new google.maps.Point(
          labelPosition.single_x,
          labelPosition.single_y,
        );
      }
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
    currentMarker.addListener('click', () => {
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
      dispatchCustomEvent('markerClick', { marker: currentMarker, currentMarkerSettings });
    });

    if (showInfoWindow === true) {
      google.maps.event.addListener(currentInfoWindow, 'closeclick', () => {
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

  /**
   * Remove marker(s) from map.
   */
  removeMapMarker = (map = null) => {
    map = map || this.map;
    map.mapMarkers.forEach((marker) => {
      marker.setMap();
    });
  };

  closeAllInfoWindow = (map = null) => {
    map = map || this.map;
    map.mapMarkers.forEach((marker) => {
      marker.infoWindow.close(map, marker);
    });
  }

}
