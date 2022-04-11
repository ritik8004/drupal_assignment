import React from 'react';
import ConditionalView from '../../../../../../js/utilities/components/conditional-view';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';

export class InfoPopUp extends React.Component {
  getDirection = (store) => {
    const { position } = store;
    return `https://www.google.com/maps/dir/Current+Location/${position.lat},${position.lng}`;
  }

  render() {
    const { selectedPlace } = this.props;
    return (
      <div>
        <div className="scroll-fix">
          <div className="location-content">
            <div className="views-field views-field-title">
              <span className="field-content">{selectedPlace.name}</span>
            </div>
            <div className="views-field views-field-field-store-address">
              <div className="field-content">
                <ConditionalView condition={hasValue(selectedPlace.address)}>
                  {selectedPlace.address.map((item) => (
                    <div key={item.code}>
                      <div className="address--line1">
                        {item.code === 'address_building_segment' ? <span>{item.value}</span> : null}
                      </div>
                      <div className="address--line2">
                        {item.code === 'street' ? <span>{item.value}</span> : null}
                      </div>
                    </div>
                  ))}
                </ConditionalView>
              </div>
            </div>
            <div className="views-field views-field-field-store-open-hours marker-hours">
              <div className="field-content">
                <div className="hours--wrapper selector--hours">
                  <div className="hours--label">
                    {Drupal.t('Opening Hours')}
                  </div>
                  <div className="open--hours">
                    {selectedPlace.openHours.map((item) => (
                      <div key={item.code}>
                        <span className="key-value-key">{item.label}</span>
                        <span className="key-value-value">{item.value}</span>
                      </div>
                    ))}
                  </div>
                </div>
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
