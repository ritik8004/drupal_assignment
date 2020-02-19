import React from 'react';

const StoreItem = ({ store }) => {
  return (
    <>
      <span className="store-name-and-address">
        <span className="store-name">{ store.name }</span>
        <span className="store-address">{ store.address }</span>
      </span>
      <div className="store-delivery-time">
        <span className="label--delivery-time">{ Drupal.t('Collect in store from') }</span>
        <span className="delivery--time--value">{ store.delivery_time }</span>
      </div>
      <div className="store-open-hours">
        <div className="hours--wrapper selector--hours">
            <div className="hours--label">{ Drupal.t('Opening Hours')  }</div>
            <div className="open--hours">
              {store.open_hours.map(function(item) {
                return (
                  <div key={ item.key }>
                    <span className="key-value-key">{ item.key }</span>
                    <span className="key-value-value">{ item.value }</span>
                  </div>
                );
              })}
            </div>
        </div>
      </div>
      <div className="store-actions" gtm-store-address="{store.address.replace(/(<([^>]+)>)/ig,'')}" gtm-store-title="{ store.name }">
        <a href="#" className="select-store">{ Drupal.t('select this store') }</a>
      </div>
    </>
  );
}

export default StoreItem;