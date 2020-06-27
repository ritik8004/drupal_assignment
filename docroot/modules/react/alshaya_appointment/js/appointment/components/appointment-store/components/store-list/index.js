import React from 'react';
import { getDistanceBetweenCoords } from '../../../../../utilities/helper';

export default class StoreList extends React.Component {
  getDistance() {
    const { storeList, coords } = this.props;
    const storeItems = getDistanceBetweenCoords(storeList, coords);

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
