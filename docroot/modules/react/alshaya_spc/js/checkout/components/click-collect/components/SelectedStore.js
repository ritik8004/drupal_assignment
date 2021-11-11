import React, { useEffect, useState } from 'react';
import ContactInfoForm from '../../contact-info-form';
import StoreItem from './StoreItem';
import SectionTitle from '../../../../utilities/section-title';
import CheckoutMessage from '../../../../utilities/checkout-message';
import { smoothScrollTo } from '../../../../utilities/smoothScroll';
import getStringMessage from '../../../../utilities/strings';
import { getCnCModalContactSubtitle } from '../../../../utilities/cnc_util';
import collectionPointsEnabled from '../../../../../../js/utilities/pudoAramaxCollection';

const SelectedStore = ({ store, open, closePanel }) => {
  const [messageType, setMsgType] = useState(null);
  const [errorSuccessMessage, setErrorMessage] = useState(null);
  let didUnmount = false;

  /**
   * Show to error on popup.
   */
  const handleScrollTo = () => {
    const container = document.querySelector('.spc-cnc-selected-store-content');
    container.scrollBy({
      top: -300,
      left: 0,
      behavior: 'smooth',
    });
  };

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

    setTimeout(() => {
      handleScrollTo();
    }, 200);
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
        <div className="spc-cnc-selected-store-content">
          {errorSuccessMessage !== null
            && (
            <CheckoutMessage type={messageType} context="selected-store-form-modal modal">
              {errorSuccessMessage}
            </CheckoutMessage>
            )}
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
            <ContactInfoForm subTitle={getStringMessage('cnc_contact_info_subtitle')} store={store} handleScrollTo={handleScrollTo} errorSuccessMessage={errorSuccessMessage} />
          </div>
        </div>
      </div>
    );
};

export default SelectedStore;
