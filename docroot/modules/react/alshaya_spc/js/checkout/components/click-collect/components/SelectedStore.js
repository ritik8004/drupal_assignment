import React, { useEffect, useState } from 'react';
import ContactInfoForm from '../../contact-info-form';
import StoreItem from './StoreItem';
import SectionTitle from '../../../../utilities/section-title';
import CheckoutMessage from '../../../../utilities/checkout-message';
import { smoothScrollTo } from '../../../../utilities/smoothScroll';
import getStringMessage from '../../../../utilities/strings';
import { getCnCModalContactSubtitle, collectionPointsEnabled } from '../../../../utilities/cnc_util';

const SelectedStore = ({ store, open, closePanel }) => {
  const [messageType, setMsgType] = useState(null);
  const [errorSuccessMessage, setErrorMessage] = useState(null);
  let didUnmount = false;

  /**
   * Show error on popup.
   */
  const handleAddressPopUpError = (e) => {
    const { type, message } = e.detail;
    if ((type !== 'error') && (didUnmount || !store)) {
      return;
    }
    setMsgType(type);
    setErrorMessage(message);
    // Scroll to error.
    smoothScrollTo('.spc-cnc-selected-store-header .spc-checkout-section-title');
  };

  // This will run on componentDidMount (only once).
  useEffect(() => {
    // Handle error on popup.
    document.addEventListener('addressPopUpError', handleAddressPopUpError, false);
    return () => {
      didUnmount = true;
      // Handle error on popup.
      document.removeEventListener('addressPopUpError', handleAddressPopUpError, false);
    };
  }, []);

  // This will run always when store value changes.
  useEffect(() => {
    // This will run when store changes and we unsets
    // any error message set previously.
    setMsgType(null);
    setErrorMessage(null);
  }, [store]);

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
        <div className="spc-cnc-selected-store-content">
          <SectionTitle>{getStringMessage(getCnCModalContactSubtitle())}</SectionTitle>
          <div className="store-details-wrapper">
            <StoreItem display="default" store={store} />
            {collectionPointsEnabled() === true
            && (
              <div className="spc-cnc-selected-store-pudo-info">
                {getStringMessage('cnc_contact_info_subtitle')}
              </div>
            )}
          </div>
          <div className="spc-cnc-contact-form">
            <ContactInfoForm subTitle={getStringMessage('cnc_contact_info_subtitle')} store={store} />
          </div>
        </div>
      </div>
    );
};

export default SelectedStore;
