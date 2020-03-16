import React from 'react';
import ContactInfoForm from '../../contact-info-form';
import StoreItem from './StoreItem';
import SectionTitle from '../../../../utilities/section-title';

const SelectedStore = ({ store, open, closePanel }) => {
  if (!store) {
    return (null);
  }

  return (
    <div id="click-and-collect-selected-store" style={{ display: open ? 'block' : 'none', width: '100%' }}>
      <div className="spc-cnc-selected-store-header">
        <span className="spc-cnc-selected-store-back" onClick={() => closePanel()} role="button" tabIndex="0" onKeyUp={() => {}} aria-label="Back" />
        <SectionTitle>{Drupal.t('collection details')}</SectionTitle>
      </div>
      <SectionTitle>{Drupal.t('selected store')}</SectionTitle>
      <div className="store-details-wrapper">
        <StoreItem display="default" store={store} />
      </div>
      <div className="spc-cnc-contact-form">
        <ContactInfoForm subTitle={Drupal.t('We will send you a text message once your order is ready for collection.')} store={store} />
      </div>
    </div>
  );
};

export default SelectedStore;
