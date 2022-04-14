import React from 'react';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import ConditionalView from '../../../../../../js/utilities/components/conditional-view';
import AddressHours from '../../AddressHours';

export class InfoPopUp extends React.Component {
  getDirection = (store) => {
    const { position } = store;
    return `https://www.google.com/maps/dir/Current+Location/${position.lat},${position.lng}`;
  }

  render() {
    const { selectedPlace, storeHours } = this.props;
    const { address } = selectedPlace;
    return (
      <div>
        <div className="scroll-fix">
          <div className="location-content">
            <div className="views-field views-field-title">
              <span className="field-content">{selectedPlace.name}</span>
            </div>
            <div className="views-field views-field-field-store-address">
              <div className="field-content">
                <ConditionalView condition={hasValue(address)}>
                  <AddressHours
                    type="addresstext"
                    address={address}
                    classname="address--line2"
                  />
                </ConditionalView>
              </div>
            </div>
            <div className="views-field views-field-field-store-open-hours marker-hours">
              <div className="field-content">
                <ConditionalView condition={hasValue(storeHours)}>
                  <div className="hours--wrapper selector--hours">
                    <div className="hours--label">
                      {Drupal.t('Opening Hours')}
                    </div>
                    <AddressHours
                      type="hourstext"
                      storeHours={storeHours}
                    />
                  </div>
                </ConditionalView>
                <div className="get--directions">
                  <a
                    className="device__desktop"
                    href={this.getDirection(selectedPlace)}
                  >
                    {Drupal.t('Get directions')}
                  </a>
                  <a className="device__tablet" href={this.getDirection(selectedPlace)}>
                    {Drupal.t('Get directions')}
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

export default InfoPopUp;
