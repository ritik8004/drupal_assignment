import React from 'react';
import { getDistanceBetweenCoords } from '../../../../../utilities/helper';

export default class StoreList extends React.Component {
  componentDidMount() {
    const { storeList } = this.props;
    const { latitude, longitude } = drupalSettings.alshaya_appointment.store_finder;
    const storeItems = getDistanceBetweenCoords(storeList, { lat: latitude, lng: longitude });

    this.handleStateChange(storeItems);
  }

  handleStateChange = (storeItems) => {
    const { handleStateChange } = this.props;
    handleStateChange(storeItems);
  }

  handleStoreSelect = (e) => {
    const { handleStoreSelect } = this.props;
    handleStoreSelect(e);
  }

  render() {
    const { storeList, activeItem } = this.props;

    return (
      <div className="store-list-inner-wrapper">
        {storeList && Object.entries(storeList).map(([k, v]) => (
          <div className="store-list-item">
            <input
              type="radio"
              value={JSON.stringify(v)}
              name="selectedStoreItem"
              checked={activeItem === v.locationExternalId}
              onChange={this.handleStoreSelect}
            />
            <span className="store-name">{v.name}</span>
            <span className="distance">
              { v.distanceInMiles }
              { Drupal.t('Miles') }
            </span>
          </div>
        ))}
      </div>
    );
  }
}
