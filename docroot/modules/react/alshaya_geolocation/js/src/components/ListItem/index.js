import React from 'react';

export class ListItem extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      open: false,
    };
  }

  toggleOpenClass = () => {
    this.setState((prevState) => ({
      ...prevState,
      open: !prevState.open,
    }));
  }

  getDirection = (store) => {
    window.open(`https://www.google.com/maps/dir/Current+Location/${store.latitude},${store.longitude}`, '_blank');
  }

  render() {
    const { specificPlace } = this.props;
    const { open } = this.state;
    return (
      <div>
        <a className="row-title">
          <span>{specificPlace.store_name}</span>
        </a>
        <div className="views-row">
          <div className="views-field-field-store-address">
            <div className="field-content">
              <div className="address--line2">
                {specificPlace.address.map((item) => (
                  <div key={item.code}>
                    {item.code === 'address_building_segment' ? item.value : null}
                    {item.code === 'street' ? <span>{item.value}</span> : null}
                  </div>
                ))}
              </div>
              <div className="field field--name-field-store-phone field--type-string field--label-hidden field__item">
                {specificPlace.store_phone}
              </div>
            </div>
          </div>
          <div className="views-field-field-store-open-hours">
            <div className="field-content">
              <div className="hours--wrapper selector--hours">
                <div>
                  <div className={open ? 'hours--label open' : 'hours--label'} onClick={this.toggleOpenClass}>
                    {Drupal.t('Opening Hours')}
                  </div>
                  <div className="open--hours">
                    {specificPlace.store_hours.map((item) => (
                      <div key={item.code}>
                        <span className="key-value-key">{item.label}</span>
                        <span className="key-value-value">{item.value}</span>
                      </div>
                    ))}
                  </div>
                </div>
              </div>
              <div className="view-on--map">
                <a onClick={() => this.getDirection(specificPlace)}>{Drupal.t('Get directions')}</a>
              </div>
              <div className="get--directions">
                <div>
                  <a
                    className="device__desktop"
                    onClick={() => this.getDirection(specificPlace)}
                  >
                    {Drupal.t('Get directions')}
                  </a>
                  <a className="device__tablet" onClick={() => this.getDirection(specificPlace)}>
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

export default ListItem;
