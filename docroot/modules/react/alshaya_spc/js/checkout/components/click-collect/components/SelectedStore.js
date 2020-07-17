import React, { useEffect, useState } from 'react';
import ContactInfoForm from '../../contact-info-form';
import StoreItem from './StoreItem';
import SectionTitle from '../../../../utilities/section-title';
import CheckoutMessage from '../../../../utilities/checkout-message';
import { smoothScrollTo } from '../../../../utilities/smoothScroll';
import getStringMessage from '../../../../utilities/strings';

const SelectedStore = ({ store, open, closePanel }) => {
  const [messageType, setMsgType] = useState(null);
  const [errorSuccessMessage, setErrorMessage] = useState(null);
  let didUnmount = false;

  /**
   * Show error on popup.
   */
  const handleAddressPopUpError = (e) => {
    if (didUnmount || !store) {
      return;
    }
    const { type, message } = e.detail;
    setMsgType(type);
    setErrorMessage(message);
    // Scroll to error.
    smoothScrollTo('.spc-cnc-selected-store-header .spc-checkout-section-title');
  };

  useEffect(() => {
    // Handle error on popup.
    document.addEventListener('addressPopUpError', handleAddressPopUpError, false);
    return () => {
      didUnmount = true;
      // Handle error on popup.
      document.removeEventListener('addressPopUpError', handleAddressPopUpError, false);
    };
  }, []);

  return (!store)
    ? null
    : (
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
