import React from 'react';
import Parser from 'html-react-parser';
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
      results,
      address,
      labels,
    } = this.props;

    return (
      <div className={isOpen ? 'click-collect-modal-wrapper click-collect-modal-wrapper-open' : 'click-collect-modal-wrapper'}>
        <div className="click-collect-all-stores inline-modal-wrapper desc-open modal-wrapper-mobile" id="click-collect-all-stores">
          <span className="close-inline-modal" onClick={onClose} />
          <div className="all-stores-info">
            <h3>{labels.title}</h3>
          </div>
          <div className="text">
            <div>{Parser(labels.help_text)}</div>
            <div className="available_store">
              <div className="available-store-text">
                <span className="store-available-at-title">
                  {Drupal.t('Available at @count stores near', { '@count': results })}
                </span>
                <div className="google-store-location">{address}</div>
              </div>
            </div>
            <p>{labels.subtitle}</p>
            <div className="store-finder-form-wrapper">
              <div id="all-stores-search-store" className="search-store" />
            </div>
          </div>
          <div className="stores-list-all">
            <ul>
              {Object.keys(stores).map(([keyItem]) => (
                <li key={stores[keyItem].id}>
                  <span className="select-store">
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
