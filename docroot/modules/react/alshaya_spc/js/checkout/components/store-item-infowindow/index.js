import React from 'react'

const StoreItemInfoWindow = ({store}) => {
  return (
    <>
      <div className="store-name-and-address">
        <span className="store-name">{ store.name }</span>
        <span className="store-address">{ store.address }</span>
      </div>
      <div className="store-open-hours">
        <div className="hours--wrapper selector--hours">
            <div className="hours--label" onClick="jQuery(this).toggleClass('open');">{ Drupal.t('Opening Hours') }</div>
            <div className="open--hours">
              {store.open_hours.map(function(item) {
                return (
                  <div key={item.key}>
                    <span className="key-value-key">{ item.key }</span>
                    <span className="key-value-value">{ item.value }</span>
                  </div>
                );
              })}
            </div>
        </div>
      </div>
      <div className="store-delivery-time">
        <span className="label--delivery-time">{ Drupal.t('Collect in store from')}</span>
        <span className="delivery--time--value">{ store.delivery_time }</span>
      </div>
    </>
  );
}

export default StoreItemInfoWindow;
