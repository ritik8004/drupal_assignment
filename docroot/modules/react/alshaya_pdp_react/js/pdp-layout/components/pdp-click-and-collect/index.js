import React from 'react';
import PdpSectionTitle from '../utilities/pdp-section-title';
import ClickCollectStoreDetail from '../pdp-click-and-collect-store-detail';
import PdpSectionText from '../utilities/pdp-section-text';
import ClickCollectContent from '../pdp-click-and-collect-popup';
import PdpClickCollectSearch from '../pdp-click-and-collect-search';

export default class PdpClickCollect extends React.PureComponent {
  constructor(props) {
    super(props);
    this.state = {
      label: 'check in-store availablility:',
      countLabel: false,
      stores: [{
        id: 'store-detail-1',
        status_color: '#0abb76',
        status_text: 'In stock',
        address_title: 'avenue mall phase 2b',
        address_details: 'mezannine above carrefour. the avenues mall phase-2 al rai',
      }, {
        id: 'store-detail-2',
        status_color: '#cc0000',
        status_text: 'Only a few left',
        address_title: 'avenue mall phase 2b',
        address_details: 'mezannine above carrefour. the avenues mall phase-2 al rai',
      }, {
        id: 'store-detail-3',
        status_color: '#0abb76',
        status_text: 'In Stock',
        address_title: 'avenue mall phase 2b',
        address_details: 'mezannine above carrefour. the avenues mall phase-2 al rai',
      }],
      location: '',
      hideInput: false,
      searchText: '',
    };
  }

  openModal = () => {
    document.querySelector('body').classList.add('click-collect-overlay');
  };

  closeModal = () => {
    document.querySelector('body').classList.remove('click-collect-overlay');
  };

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

  render() {
    const {
      skuCode, finalPrice, pdpProductPrice, title,
    } = this.props;
    const {
      label, countLabel, stores, location, hideInput, searchText,
    } = this.state;

    return (
      <div className="magv2-pdp-click-and-collect-wrapper card">
        <div className="magv2-click-collect-title-wrapper">
          <PdpSectionTitle>
            {Drupal.t('click & collect')}
            <span className="click-collect-title-tag free-tag">{Drupal.t('free')}</span>
          </PdpSectionTitle>
        </div>
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
        {countLabel ? stores.filter((store, key) => key < 2).map((store, key) => <ClickCollectStoreDetail key={store.id} index={key + 1} store={store} />) : ''}
        {countLabel ? (
          <div className="magv2-click-collect-showmore-link" onClick={() => this.openModal()}>
            {Drupal.t('View all')}
          </div>
        ) : '' }
        <ClickCollectContent
          closeModal={this.closeModal}
          title={title.label}
          pdpProductPrice={pdpProductPrice}
          finalPrice={finalPrice}
          skuCode={skuCode}
          stores={stores}
          key={title.label}
        />
      </div>
    );
  }
}
