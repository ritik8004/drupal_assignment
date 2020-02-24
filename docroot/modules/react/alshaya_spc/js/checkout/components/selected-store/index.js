import React from 'react'
import parse from 'html-react-parser';
import ContactInfoForm from '../contact-info-form';
import StoreItem from '../store-item';

const SelectedStore = ({ store, open }) => {
  if (!store) {
    return (null);
  }

  return (
    <div id="click-and-collect-selected-store" style={{ display: open ? 'block' : 'none', width: '100%' }}>
      <div>{Drupal.t('Selected Store')}</div>
      <div className="store-wrapper">
        <StoreItem store={store} />
      </div>
      <div className='spc-cnc-contact-form'>
        <ContactInfoForm store={store} />
      </div>
    </div>
  )
}

export default SelectedStore;
