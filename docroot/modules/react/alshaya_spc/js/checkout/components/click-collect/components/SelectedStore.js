import React from 'react';
import ContactInfoForm from '../../contact-info-form';
import StoreItem from './StoreItem';
import SectionTitle from '../../../../utilities/section-title';
import CheckoutMessage from '../../../../utilities/checkout-message';
import getStringMessage from '../../../../utilities/strings';

const SelectedStore = ({
  store, open, closePanel, messageType, errorSuccessMessage,
}) => {
  if (!store) {
    return (null);
  }

  return (
    <div id="click-and-collect-selected-store" style={{ display: open ? 'block' : 'none', width: '100%' }}>
      <div className="spc-cnc-selected-store-header">
        <span className="spc-cnc-selected-store-back" onClick={() => closePanel()} />
        <SectionTitle>{getStringMessage('cnc_collection_details')}</SectionTitle>
      </div>
      {errorSuccessMessage !== null
        && (
        <CheckoutMessage type={messageType} context="selected-store-form-modal modal">
          {errorSuccessMessage}
        </CheckoutMessage>
        )}
      <SectionTitle>{getStringMessage('cnc_selected_store')}</SectionTitle>
      <div className="store-details-wrapper">
        <StoreItem display="default" store={store} />
      </div>
      <div className="spc-cnc-contact-form">
        <ContactInfoForm subTitle={getStringMessage('cnc_contact_info_subtitle')} store={store} />
      </div>
    </div>
  );
};

export default SelectedStore;
