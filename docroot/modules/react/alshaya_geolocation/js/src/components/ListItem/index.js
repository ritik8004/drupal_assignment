import React from 'react';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

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
      <div className="store-info-wrap">
        <span className="retinal-enabled-yes">
          <div className="field__wrapper field-store-retina-photography" />
        </span>
        <div className="store-name row-title individual-store-title"><span>{specificPlace.store_name}</span></div>
        <div className="views-row">
          <div className="store-address views-field-field-store-address">
            <div className="store-field-content field-content">
              <div className="address--line2">
                <div className="field__wrapper field-store-address">
                  {typeof specificPlace.address !== 'undefined'
                  && (
                    <>
                      {specificPlace.address.map((item) => (
                        <>
                          {item.code === 'address_building_segment' ? <span>{item.value}</span> : null}
                          {item.code === 'street' ? <span>{item.value}</span> : null}
                        </>
                      ))}
                    </>
                  )}
                </div>
                <div className="field__wrapper field-store-phone">
                  {specificPlace.store_phone}
                </div>
              </div>
            </div>
          </div>
        </div>
        <div className="views-field-field-store-open-hours">
          <div className="field-content">
            <div className="hours--wrapper selector--hours">
              <div className={open ? 'hours--label open' : 'hours--label'} onClick={this.toggleOpenClass}>
                {Drupal.t('Opening Hours')}
              </div>
              <div className="open--hours">
                <ConditionalView condition={hasValue(specificPlace.store_hours)}>
                  {specificPlace.store_hours.map((item) => (
                    <div key={item.code}>
                      <span className="key-value-key">{item.label}</span>
                      <span className="key-value-value">{item.value}</span>
                    </div>
                  ))}
                </ConditionalView>
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
    );
  }
}

export default ListItem;
