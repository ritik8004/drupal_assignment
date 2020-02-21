import _isEmpty from 'lodash/isEmpty';

export default class Gmap {

  constructor() {
    this.map = {
      settings: {
        zoom: 11,
        maxZoom: 18,
        zoomControl: true,
        fullscreenControl: false,
        mapTypeControl: true,
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
        info_auto_display: false
      },
      googleMap: null,
      geoCoder: new google.maps.Geocoder(),
      mapMarkers: []
    };

    if (typeof drupalSettings.map_marker !== 'undefined' && drupalSettings.map_marker !== 'undefined') {
      this.map.settings.map_marker.icon = drupalSettings.map_marker.icon;
      this.map.settings.map_marker.label_position = drupalSettings.map_marker.label_position;
    }
  }

  initMap(container) {
    this.map.googlemap = new google.maps.Map(container, {
      zoom: this.map.settings.zoom,
      maxZoom: this.map.settings.maxZoom,
      minZoom: this.map.settings.minZoom,
      fullscreenControl: this.map.settings.fullscreenControl,
      mapTypeId: google.maps.MapTypeId['ROADMAP'],
      mapTypeControlOptions: {
        position: google.maps.ControlPosition.LEFT_BOTTOM
      },
      zoomControl: this.map.settings.zoomControl,
      zoomControlOptions: {
        style: google.maps.ZoomControlStyle.SMALL,
        position: google.maps.ControlPosition.LEFT_CENTER
      },
      streetViewControl: this.map.settings.streetViewControl,
      streetViewControlOptions: {
        position: google.maps.ControlPosition.LEFT_CENTER
      },
      mapTypeControl: this.map.settings.mapTypeControl,
      scrollwheel: this.map.settings.scrollwheel,
      disableDoubleClickZoom: this.map.settings.disableDoubleClickZoom,
      draggable: this.map.settings.draggable,
      gestureHandling: this.map.settings.gestureHandling
    });

    return this.map.googlemap;
  }

  /**
   * Get map object.
   */
  getMapObj = () => {
    return this.map.googleMap;
  }

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
      this.map.googleMap.setZoom(11);
    }

    this.map.geoCoder.geocode({
      componentRestrictions: {
        country: window.drupalSettings.country_code
      }
    }, function (results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
        // Just center the map and don't do anything.
        window.spcMap.googleMap.setCenter(results[0].geometry.location);
        if (callBackFunc) {
          callBackFunc.call(results)
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
   */
  setMapMarker = (markerSettings, skipInfoWindow) => {
    this.map.mapMarkers = this.map.mapMarkers || [];
    skipInfoWindow = skipInfoWindow || false;

    let { icon: marker_icon_path, label_position } = this.map.settings.map_marker;

    if (typeof marker_icon_path === 'string') {
      // Add the marker icon.
      markerSettings.icon = {
        url: marker_icon_path,
        labelOrigin: new google.maps.Point(label_position.x, label_position.y),
        scaledSize: new google.maps.Size(31, 48)
      };

      // If only single digit move them closer to the center.
      if (drupalSettings.path.currentLanguage === 'ar' && markerSettings.label.length === 1) {
        markerSettings.icon.labelOrigin = new google.maps.Point(label_position.single_x, label_position.single_y);
      }
    }

    if (!markerSettings.map) {
      markerSettings.map = this.map.googleMap;
    }

    // Add the marker to the map.
    /** @type {GoogleMarker} */
    let currentMarker = new google.maps.Marker(markerSettings);

    if (skipInfoWindow !== true) {
      // Set the info popup text.
      let currentInfoWindow = new google.maps.InfoWindow({
        content: markerSettings.infoWindowContent,
        maxWidth: 209,
        disableAutoPan: this.map.disableAutoPan
      });

      let map = this.map;
      currentMarker.addListener('click', function () {
        if (markerSettings.infoWindowSolitary) {
          if (typeof map.infoWindow !== 'undefined') {
            map.infoWindow.close();
          }
          map.infoWindow = currentInfoWindow;
        }
        // Set the marker to center of the map on click.
        map.googleMap.setCenter(currentMarker.getPosition());
        map.googleMap.setZoom(11);
        map.googleMap.panBy(0, -150);

        currentInfoWindow.open(map.googleMap, currentMarker);
      });

      google.maps.event.addListener(currentInfoWindow, 'closeclick', function () {
        // Auto zoom.
        map.googleMap.fitBounds(map.googleMap.bounds);
        // Auto center.
        map.googleMap.panToBounds(map.googleMap.bounds);
      });

      if (map.settings.info_auto_display) {
        google.maps.event.addListenerOnce(map.googleMap, 'tilesloaded', function () {
          google.maps.event.trigger(currentMarker, 'click');
        });
      }
    }

    this.map.mapMarkers.push(currentMarker);
    return currentMarker;
  };

  /**
   * Remove marker(s) from map.
   */
  removeMapMarker = function (map = null) {
    map = map || this.map;
    map.mapMarkers.forEach(function (item) {
      item.setMap();
    });
  };

}
