import React from 'react'
import parse from 'html-react-parser';
import ContactInfoForm from '../contact-info-form';

const SelectedStore = ({ store, open }) => {
  if (!store) {
    return (null);
  }

  return (
    <div id="click-and-collect-selected-store" style={{ display: open ? 'block' : 'none', width: '100%' }}>
      <div>{Drupal.t('Selected Store')}</div>
      <div className="store-wrapper">
        <span className="store-name-and-address">
          <span className="store-name">{store.name}</span>
          <span className="store-address">{parse(store.address)}</span>
        </span>
        <div className="store-delivery-time">
          <span className="label--delivery-time">{Drupal.t('Collect in store from')}</span>
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
      </div>
      <div className='spc-cnc-contact-form'>
        <ContactInfoForm store={store} />
      </div>
    </div>
  )
}

export default SelectedStore;
