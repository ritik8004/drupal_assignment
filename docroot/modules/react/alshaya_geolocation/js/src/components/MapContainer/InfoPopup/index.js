import React from 'react';

export class InfoPopUp extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      open: false,
    };
  }

  getDirection = (store) => {
    const { position } = store;
    return `https://www.google.com/maps/dir/Current+Location/${position.lat},${position.lng}`;
  }

  render() {
    const { selectedPlace } = this.props;
    const { open } = this.state;
    return (
      <div>
        <div className="scroll-fix">
          <div className="location-content">
            <div className="views-field views-field-title">
              <span className="field-content">{selectedPlace.name}</span>
            </div>
            <div className="views-field views-field-field-store-address">
              <div className="field-content">
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
              </div>
            </div>
            <div className="views-field views-field-field-store-open-hours marker-hours">
              <div className="field-content">
                <div className="hours--wrapper selector--hours">
                  <div className={open ? 'hours--label open' : 'hours--label'}>
                    {Drupal.t('Opening Hours')}
                  </div>
                  <div className="open--hours">
                    {selectedPlace.openHours.map((item) => (
                      <div key={item.code}>
                        <div>
                          <span className="key-value-key">{item.label}</span>
                          <span className="key-value-value">{item.value}</span>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
                <div className="get--directions">
                  <div>
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
      </div>
    );
  }
}

export default InfoPopUp;
