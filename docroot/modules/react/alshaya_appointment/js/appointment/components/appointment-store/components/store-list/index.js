import React from 'react';
import { getDistanceBetweenCoords } from '../../../../../utilities/helper';
import ConditionalView from '../../../../../common/components/conditional-view';
import StoreAddress from '../store-address';
import StoreTiming from '../store-timing';

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
    const {
      storeList, activeItem, display, onStoreExpand,
    } = this.props;


    return (
      <div className="store-list-inner-wrapper fadeInUp">
        {storeList && Object.entries(storeList).map(([k, v]) => (
          <div className="store-list-item">
            <input
              type="radio"
              id={`store${k}`}
              value={JSON.stringify(v)}
              name="selectedStoreItem"
              checked={activeItem === v.locationExternalId}
              onChange={this.handleStoreSelect}
            />
            <label htmlFor={`store${k}`} className="select-store">
              <span className="appointment-store-name">
                <span className="store-name-wrapper">
                  <span className="store-name">{v.name}</span>
                  <span className="distance">
                    { v.distanceInMiles }
                    { Drupal.t('Miles') }
                  </span>
                </span>
                <ConditionalView condition={display === 'accordion'}>
                  <span className="expand-btn" onClick={(e) => onStoreExpand(e)}>Expand</span>
                </ConditionalView>
              </span>
              <ConditionalView condition={display === 'accordion'}>
                <div className="store-address-content">
                  <div className="store-address">
                    <StoreAddress
                      address={v.address}
                    />
                  </div>
                  <div className="store-delivery-time">
                    <StoreTiming
                      timing={v.storeTiming}
                    />
                  </div>
                </div>
              </ConditionalView>
            </label>
          </div>
        ))}
      </div>
    );
  }
}
