import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import AddressHours from '../AddressHours';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';

export class ListItem extends React.Component {
  toggleOpenClass = (storeId) => {
    const element = document.getElementById(`hours--label-${storeId}`);
    element.classList.toggle('open');
  }

  getDirection = (store) => {
    window.open(`https://www.google.com/maps/dir/Current+Location/${store.latitude},${store.longitude}`, '_blank');
  }

  render() {
    const { specificPlace, storeHours } = this.props;
    const { address } = specificPlace;
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
                <ConditionalView condition={hasValue(address)}>
                  <AddressHours
                    type="addresstext"
                    address={address}
                    classname="field__wrapper field-store-address"
                  />
                </ConditionalView>
                <div className="field__wrapper field-store-phone">
                  {specificPlace.store_phone}
                </div>
              </div>
            </div>
          </div>
        </div>
        <div className="views-field-field-store-open-hours">
          <div className="field-content">
            <ConditionalView condition={hasValue(storeHours)}>
              <div className="hours--wrapper selector--hours">
                <div id={`hours--label-${specificPlace.id}`} className="hours--label" onClick={() => this.toggleOpenClass(specificPlace.id)}>
                  {Drupal.t('Opening Hours')}
                </div>
                <AddressHours
                  type="hourstext"
                  storeHours={storeHours}
                />
              </div>
            </ConditionalView>
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
