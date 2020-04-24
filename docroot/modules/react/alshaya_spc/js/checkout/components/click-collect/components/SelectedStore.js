import React, { useEffect, useState } from 'react';
import ContactInfoForm from '../../contact-info-form';
import StoreItem from './StoreItem';
import SectionTitle from '../../../../utilities/section-title';
import CheckoutMessage from '../../../../utilities/checkout-message';
import { smoothScrollTo } from '../../../../utilities/smoothScroll';

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
          <SectionTitle>{Drupal.t('collection details')}</SectionTitle>
        </div>
        {errorSuccessMessage !== null
          && (
          <CheckoutMessage type={messageType} context="selected-store-form-modal modal">
            {errorSuccessMessage}
          </CheckoutMessage>
          )}
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
