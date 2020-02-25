import React from 'react';
import parse from 'html-react-parser';

const StoreItem = ({ store, onSelectStore }) => {
  return (
    <React.Fragment>
      <span className="spc-cnc-store-name">
        <span className="store-name">{store.name}</span>
        <span className="store-distance">{store.formatted_distance}</span>
      </span>
      <div className="store-address-content">
        <div className="store-address">{parse(store.address)}</div>
        <div className="store-delivery-time">
          <span className="label--delivery-time">{Drupal.t('Collect in store from')}</span>
          <span className="delivery--time--value">{store.delivery_time}</span>
        </div>
        <div className="store-open-hours">
          {
            Object.entries(store.open_hours_group).map(([weekdays, timings]) => (
              <div key={weekdays}>
                <span className="key-value-key">{weekdays}</span>
                <span className="key-value-value">({timings})</span>
              </div>
            ))
          }
        </div>
        {
          typeof onSelectStore !== 'undefined' && (
            <div className="store-actions" gtm-store-address={store.address.replace(/(<([^>]+)>)/ig, '')} gtm-store-title={store.name}>
              <button className="select-store" onClick={(e) => onSelectStore(e, store.code)}>{Drupal.t('select this store')}</button>
            </div>
          )
        }
      </div>
    </React.Fragment>
  );
};

export default StoreItem;
