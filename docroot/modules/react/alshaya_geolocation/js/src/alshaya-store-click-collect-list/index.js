import React from 'react';
import Axios from 'axios';
import Popup from 'reactjs-popup';
import AutocompleteSearch from '../components/autocomplete-search';
import { ListItemClick } from '../components/ListItemClick';

export class StoreClickCollectList extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      stores: [],
      count: 1,
      showListingView: false,
      specificPlace: {},
      center: {},
      newCenter: {},
      zoom: 10,
      active: false,
      open: false,
      showAutomcomplete: true,
      area: '',
    };
  }

  componentDidMount() {
    // This will be replace with MDC data api call.
    const apiUrl = '/alshaya-locations/stores-list';
    Axios.get(apiUrl).then((response) => {
      const stores = response.data;
      const prevState = this.state;
      this.setState(
        {
          ...prevState,
          stores: stores.items,
          count: stores.total_count,
          center: { lat: stores.items[0].latitude, lng: stores.items[0].longitude },
        },
      );
    });
  }

  toggleClass() {
    const { active } = this.state;
    this.setState({ active: !active });
  }

  searchStores = (place) => {
    if (place.geometry !== undefined) {
      const currentLocation = JSON.parse(JSON.stringify(place.geometry.location));
      const { stores } = this.state;
      const nearbyStores = this.nearByStores(stores, currentLocation);
      const prevState = this.state;
      this.setState({
        ...prevState,
        area: place,
        stores: nearbyStores,
        count: nearbyStores.length,
        showListingView: true,
        showAutomcomplete: false,
      });
    }
  }

  nearByStores = (stores, currentLocation) => {
    const nearbyStores = stores.filter((store) => {
      const otherLocation = { lat: +store.latitude, lng: +store.longitude };
      const distance = this.getDistanceBetween(currentLocation, otherLocation);
      return (distance < 5) ? store : null;
    });
    return nearbyStores;
  }

  getDistanceBetween = (location1, location2) => {
    // The math module contains a function
    // named toRadians which converts from
    // degrees to radians.

    const lon1 = (parseInt((location1.lng), 10) * Math.PI) / 180;
    const lon2 = (parseInt((location2.lng), 10) * Math.PI) / 180;
    const lat1 = (parseInt((location1.lat), 10) * Math.PI) / 180;
    const lat2 = (parseInt((location1.lat), 10) * Math.PI) / 180;

    // Haversine formula
    const dlon = lon2 - lon1;
    const dlat = lat2 - lat1;
    const a = (Math.sin(dlat / 2) ** 2)
      + Math.cos(lat1) * Math.cos(lat2)
      * (Math.sin(dlon / 2) ** 2);

    const c = 2 * Math.asin(Math.sqrt(a));
    // Radius of earth in kilometers.
    const r = 6371;
    // calculate the result
    return (c * r);
  }

  render() {
    const {
      stores,
      showListingView,
      open,
      area,
      showAutomcomplete,
      active,
    } = this.state;
    const shorts = stores.slice(0, 2);
    const cncLabels = drupalSettings.cac;
    return (
      <>
        <div className="views-content">

          <div id="pdp-stores-container" className="click-collect">
            <h3 className="c-accordion__title" onClick={() => this.toggleClass()}>
              <span className="pdp-stores-container">{cncLabels.title}</span>
              <span claclassNamess="subtitle">{cncLabels.subtitle}</span>
            </h3>
            <div className={active ? 'active' : 'hidden'}>
              <div>{cncLabels.help_text}</div>
              {showAutomcomplete
                ? (
                  <div>
                    <div>{Drupal.t('Check in-store availability')}</div>
                    <AutocompleteSearch placeholder={Drupal.t('Enter a location')} searchStores={(place) => this.searchStores(place)} />
                  </div>
                )
                : (
                  <div>
                    <span>
                      {Drupal.t('Available at ')}
                      {stores.length}
                      {Drupal.t(' stores near ')}
                      {area.formatted_address}
                    </span>
                    <span onClick={() => this.setState({ showAutomcomplete: true })}>
                      <b>{Drupal.t('Change')}</b>
                    </span>
                  </div>
                )}
              {showListingView
              && (
                <div className="view-content">
                  <div id="click-and-collect-list-view">
                    <ul>
                      {Object.keys(shorts).map(([keyItem]) => (
                        <li>
                          <span
                            key={shorts[keyItem].id}
                            className="select-store"
                          >
                            <ListItemClick specificPlace={shorts[keyItem]} />
                          </span>
                        </li>
                      ))}
                      {(stores.length > 2
                      && (
                        <li>
                          <button type="button" onClick={() => this.setState({ open: true })}>
                            {Drupal.t('Other stores nearby')}
                          </button>
                        </li>
                      ))}
                    </ul>
                  </div>
                </div>
              )}
              <Popup
                className="area-popups"
                open={open}
                closeOnEscape={false}
              >
                <button type="button" onClick={() => this.setState({ open: false })}>X</button>
                <div>
                  <ul>
                    {Object.keys(stores).map(([keyItem]) => (
                      <li>
                        <span
                          key={stores[keyItem].id}
                          className="select-store"
                        >
                          <ListItemClick specificPlace={stores[keyItem]} />
                        </span>
                      </li>
                    ))}
                  </ul>
                </div>
              </Popup>

            </div>
          </div>
        </div>

      </>
    );
  }
}
export default StoreClickCollectList;
