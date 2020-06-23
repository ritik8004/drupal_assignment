import React from 'react';
import PdpSectionTitle from '../utilities/pdp-section-title';
import ClickCollectStoreDetail from '../pdp-click-and-collect-store-detail';
import PdpSectionText from '../utilities/pdp-section-text';
import PdpClickCollectSearch from '../pdp-click-and-collect-search';

export default class PdpClickCollect extends React.PureComponent {
  constructor(props) {
    super(props);
    this.state = {
      label: 'check in-store availablility:',
      countLabel: false,
      stores: [{
        id: 'store-detail-1',
        address_title: 'avenue mall phase 2b',
        address_details: 'mezannine above carrefour. the avenues mall phase-2 al rai',
      }, {
        id: 'store-detail-2',
        address_title: 'avenue mall phase 2b',
        address_details: 'mezannine above carrefour. the avenues mall phase-2 al rai',
      }, {
        id: 'store-detail-3',
        address_title: 'avenue mall phase 2b',
        address_details: 'mezannine above carrefour. the avenues mall phase-2 al rai',
      }],
      location: '',
      hideInput: false,
      searchText: '',
      showMore: false,
    };
  }

  textChange = (text) => {
    const txtLimitExceeded = (text.length > 3);
    this.setState({
      label: txtLimitExceeded ? 'in-store availability:' : 'check in-store availablility:',
      countLabel: txtLimitExceeded,
      location: text,
      hideInput: txtLimitExceeded,
      searchText: text,
    });
  }

  showInput = () => {
    this.setState({
      hideInput: false,
    });
  }

  toggleShowMore = () => {
    this.setState((prevState) => ({
      showMore: !prevState.showMore,
    }));
  }

  showClickCollectContent = () => {
    document.querySelector('.magv2-pdp-click-and-collect-wrapper').classList.toggle('show-click-collect-content');
  }

  render() {
    const {
      label, countLabel, stores, location, hideInput, searchText, showMore,
    } = this.state;

    return (
      <div className="magv2-pdp-click-and-collect-wrapper card">
        <div className="magv2-click-collect-title-wrapper">
          <PdpSectionTitle>
            {Drupal.t('click & collect')}
            <span className="click-collect-title-tag free-tag">{Drupal.t('free')}</span>
          </PdpSectionTitle>
          <div className="magv2-accordion" onClick={this.showClickCollectContent} />
        </div>
        <div className="magv2-click-collect-content-wrapper">
          <PdpSectionText className="click-collect-detail">
            <span>{Drupal.t('order now and collect from the store of your choice in 1 to 2 days.')}</span>
          </PdpSectionText>
          <div className="instore-wrapper">
            <div className="instore-title">{label}</div>
            {countLabel ? (
              <div className="store-count-label">
                {Drupal.t('2 Stores at')}
                {' '}
                <span className="location-name" onClick={this.showInput}>{ location }</span>
              </div>
            ) : ''}
            {hideInput ? '' : <PdpClickCollectSearch inputValue={searchText} onChange={this.textChange} />}
          </div>
          {countLabel ? stores.filter((store, key) => key < (showMore ? stores.length : 2)).map((store, key) => <ClickCollectStoreDetail key={store.id} index={key + 1} store={store} />) : ''}
          {countLabel ? (
            <div className="magv2-click-collect-show-link" onClick={this.toggleShowMore}>
              {Drupal.t(showMore ? 'Show-less' : 'Show-more')}
            </div>
          ) : '' }
        </div>
      </div>
    );
  }
}
