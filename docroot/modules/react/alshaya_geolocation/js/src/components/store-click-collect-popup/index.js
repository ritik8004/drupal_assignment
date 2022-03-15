import React from 'react';
import { ListItemClick } from '../ListItemClick';

export class ClickCollectPopup extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
    };
  }

  render() {
    const {
      stores,
      isOpen,
      onClose,
    } = this.props;

    return (
      <div className={isOpen ? 'click-collect-modal-wrapper click-collect-modal-wrapper-open' : 'click-collect-modal-wrapper'}>
        <div className="click-collect-all-stores inline-modal-wrapper desc-open modal-wrapper-mobile" id="click-collect-all-stores">
          <span className="close-inline-modal" onClick={onClose} />
          <div className="all-stores-info">
            <h3>{Drupal.t('Click & Collect')}</h3>
          </div>
          <div className="text">
            <p>
              {Drupal.t('This service is')}
              <strong>{Drupal.t('free')}</strong>
              {Drupal.t('of charge.')}
            </p>
            <div className="available_store">
              <div className="available-store-text">
                <span className="store-available-at-title">{Drupal.t('Available at 12 stores near')}</span>
                <div className="google-store-location">{Drupal.t('7WPM+3M Rabia, Kuwait')}</div>
                <div className="change-location" />
                <div className="change-location-link">{Drupal.t('change')}</div>
              </div>
            </div>
            <p>{Drupal.t('Order now & collect from a store of your choice')}</p>
            <div className="store-finder-form-wrapper">
              <div id="all-stores-search-store" className="search-store" />
            </div>
          </div>
          <div className="stores-list-all">
            <ul>
              {Object.keys(stores).map(([keyItem]) => (
                <li>
                  <span key={stores[keyItem].id} className="select-store">
                    <ListItemClick specificPlace={stores[keyItem]} isPopup={false} />
                  </span>
                </li>
              ))}
            </ul>
          </div>
          <div className="gradient-holder" />
        </div>
      </div>
    );
  }
}

export default ClickCollectPopup;
