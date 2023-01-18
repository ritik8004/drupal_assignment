import React from 'react';
import Axios from 'axios';
import { ClickCollectPopup } from '../components/store-click-collect-popup';
import AutocompleteSearch from '../components/autocomplete-search';
import { ListItemClick } from '../components/ListItemClick';
import { nearByStores } from '../utility';

export class StoreClickCollectList extends React.PureComponent {
  constructor(props) {
    super(props);
    this.state = {
      stores: [],
      results: [],
      count: 1,
      showListingView: false,
      specificPlace: {},
      active: false,
      showAutomcomplete: true,
      area: '',
      isModalOpen: false,
    };
    this.openModal = this.openModal.bind(this);
    this.closeModal = this.closeModal.bind(this);
  }

  componentDidMount() {
    // This will be replace with MDC data api call.
    const { apiUrl } = window.alshayaGeolocation.getStoreLabelsPdp();
    Axios.get(apiUrl).then((response) => {
      const stores = response.data;
      if (Object.keys(stores).length !== 0) {
        const prevState = this.state;
        this.setState(
          {
            ...prevState,
            stores: stores.items,
            results: stores.items,
            count: stores.total_count,
          },
        );
      }
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
      const nearbyStores = nearByStores(stores, currentLocation);
      const prevState = this.state;
      this.setState({
        ...prevState,
        area: place,
        results: nearbyStores,
        count: nearbyStores.length,
        showListingView: true,
        showAutomcomplete: false,
      });
      // Push pdp search stores event to GTM if store is available
      // and selected successfully.
      Drupal.alshayaSeoGtmPushEcommerceEvents({
        eventAction: 'pdp search stores',
        eventLabel: place.formatted_address,
      });
    }
  }

  openModal() {
    this.setState({ isModalOpen: true });
  }

  closeModal() {
    this.setState({ isModalOpen: false });
  }

  render() {
    const {
      results,
      count,
      showListingView,
      area,
      showAutomcomplete,
      active,
      isModalOpen,
    } = this.state;
    const shorts = results.slice(0, 2);
    const cncLabels = window.alshayaGeolocation.getStoreLabelsPdp();
    return (
      <>
        <div className="delivery-options-wrapper">
          <div className="field__content">
            <div className="click-collect c-accordion-delivery-options ui-accordion ui-widget ui-helper-reset">
              <h3 className="c-accordion__title ui-accordion-header ui-corner-top ui-state-default ui-accordion-icons location-js-initiated ui-accordion-header-active" onClick={() => this.toggleClass()}>
                <span className="ui-accordion-header-icon ui-icon ui-icon-triangle-1-s">{cncLabels.title}</span>
                <span className="subtitle">{cncLabels.subtitle}</span>
              </h3>
              <div className="c-accordion_content ui-accordion-content ui-corner-bottom ui-helper-reset ui-widget-content">
                <div className={active ? 'active' : ''}>
                  <div className="click-collect-empty-selection" />
                  <div className="click-collect-form">
                    <div className="text">
                      {showAutomcomplete
                        ? (
                          <div className="store-finder-form-wrapper">
                            <div id="search-store" className="search-store">
                              <form className="alshaya-stores-available-stores">
                                <span className="label">{Drupal.t('Check in-store availability')}</span>
                                <div>
                                  <AutocompleteSearch
                                    placeholder={Drupal.t('Enter your area')}
                                    searchStores={this.searchStores}
                                  />
                                  <button className="search-stores-button" type="button">{Drupal.t('search stores')}</button>
                                </div>
                              </form>
                            </div>
                          </div>
                        )
                        : (
                          <div className="available_store">
                            <div className="available-store-text">
                              <span className="store-available-at-title">
                                {Drupal.t('Available at @count stores near', { '@count': count })}
                              </span>
                              <div className="google-store-location">{area.formatted_address}</div>
                              <div className="change-location-link" onClick={() => this.setState({ showAutomcomplete: true })}>{Drupal.t('change')}</div>
                            </div>
                          </div>
                        )}
                    </div>
                  </div>
                  {showListingView
                  && (
                    <div className="click-collect-top-stores">
                      <div id="click-and-collect-list-view">
                        <ul>
                          {shorts.map((store, index) => (
                            <li>
                              <span key={store.id} className="select-store">
                                <div className="store-sequence">{(index) + 1}</div>
                                <ListItemClick specificPlace={store} isPopup />
                              </span>
                            </li>
                          ))}
                        </ul>
                      </div>
                      {(results.length > 3
                      && (
                        <div className="other-stores-link" onClick={this.openModal}>
                          {Drupal.t('Other stores nearby')}
                        </div>
                      ))}
                    </div>
                  )}
                </div>
              </div>
              <ClickCollectPopup
                labels={cncLabels}
                stores={results}
                results={results.length}
                address={area.formatted_address}
                isOpen={isModalOpen}
                onClose={this.closeModal}
              />
            </div>
          </div>
        </div>
      </>
    );
  }
}
export default StoreClickCollectList;
