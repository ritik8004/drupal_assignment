import React from 'react';

export default class StoreList extends React.Component {
  getDistance() {
    const { storeList, coords } = this.props;

    const storeItems = google && storeList && Object.entries(storeList).map(([k, x]) => {
      const store = x;
      const distance = google.maps.geometry.spherical.computeDistanceBetween(
        new google.maps.LatLng(coords.lat, coords.lng),
        new google.maps.LatLng(x.geocoordinates.latitude, x.geocoordinates.longitude),
      );
      store.distanceInMiles = this.convertKmToMile(distance);
      return store;
    });

    this.handleStateChange(storeItems);
  }

  handleStateChange = (storeItems) => {
    const { handleStateChange } = this.props;
    handleStateChange(storeItems);
  }

  convertKmToMile = (value) => {
    const realMiles = (value * 0.621371);
    const Miles = Math.floor(realMiles);
    return Miles;
  }

  handleStoreSelect = (e) => {
    const { handleStoreSelect } = this.props;
    handleStoreSelect(e);
  }

  render() {
    const { storeList, activeItem } = this.props;

    return (
      <div className="store-list-inner-wrapper">
        {storeList && Object.entries(storeList).map(([k, v]) => {
          if ((typeof google !== 'undefined') && (typeof v.distanceInMiles === 'undefined')) {
            this.getDistance();
          }

          return (
            <div className="store-list-item">
              <input
                type="radio"
                value={JSON.stringify(v)}
                name="selectedStoreItem"
                checked={activeItem === k}
                onChange={this.handleStoreSelect}
              />
              <span className="store-name">{v.name}</span>
              <span className="distance">
                { v.distanceInMiles }
                { Drupal.t('Miles') }
              </span>
            </div>
          );
        })}
      </div>
    );
  }
}
