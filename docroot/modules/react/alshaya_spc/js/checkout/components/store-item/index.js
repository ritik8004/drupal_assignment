import React from 'react';
import parse from 'html-react-parser';

const StoreItem = ({ store, onSelectStore }) => {
  return (
    <React.Fragment>
      <span className='spc-cnc-store-name'>{store.name}</span>
      <div className="store-address-content">
        <div className="store-address">{parse(store.address)}</div>
        <div className="store-delivery-time">
        <span
          className="label--delivery-time">{Drupal.t('Collect in store from')}</span>
        <span className="delivery--time--value">{store.delivery_time}</span>
      </div>
        <div className="store-open-hours">
        <div className="hours--wrapper selector--hours">
            <div className="hours--label">{Drupal.t('Opening Hours')}</div>
            <div className="open--hours">
              {store.open_hours.map(function (item) {
                return (
                  <div key={item.key}>
                    <span className="key-value-key">{item.key}</span>
                    <span className="key-value-value">{item.value}</span>
                  </div>
                );
              })}
            </div>
        </div>
      </div>
        <div className="store-actions"
             gtm-store-address={store.address.replace(/(<([^>]+)>)/ig, '')}
             gtm-store-title={store.name}>
          <button className="select-store"
                  onClick={(e) => this.props.onSelectStore(e, store.code)}>{Drupal.t('select this store')}</button>
        </div>
      </div>
    </React.Fragment>
  );
};

export default StoreItem;
