import React from 'react';
import axios from 'axios';
import Popup from 'reactjs-popup';
import SectionTitle from '../../utilities/section-title';
import TextField from '../../utilities/textfield';
import dispatchCustomEvent from '../../../../js/utilities/events';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../utilities/checkout_util';

const getInputField = (shareByContext) => {
  if (shareByContext === 'wa' || shareByContext === 'sms') {
    return (
      <TextField
        type="tel"
        name="smart-agent-share-mobile"
        label={Drupal.t('Mobile Number')}
      />
    );
  }

  return (
    <TextField
      type="email"
      required
      name="smart-agent-share-email"
      label={Drupal.t('Email')}
    />
  );
};

const validateFields = () => {
  // Since we have only one input field.
  const element = document.querySelector('.smart-agent-share-modal-content-region input');
  const name = element.getAttribute('name');
  if (element.value.length < 1) {
    if (name === 'smart-agent-share-mobile') {
      document.getElementById(`${name}-error`).innerHTML = Drupal.t('Enter Mobile Number');
    } else {
      document.getElementById(`${name}-error`).innerHTML = Drupal.t('Enter Email');
    }
    document.getElementById(`${name}-error`).classList.add('error');
    return true;
  }

  document.getElementById(`${name}-error`).innerHTML = '';
  document.getElementById(`${name}-error`).classList.remove('error');
  return false;
};

const handleSubmit = (e) => {
  e.preventDefault();
  const error = validateFields();
  if (error) {
    return false;
  }
  showFullScreenLoader();
  // Get the share method type.
  const shareMethod = e.target.elements['agent-share-method'].value;
  let shareVal = '';
  if (shareMethod === 'sms') {
    shareVal = e.target.elements['smart-agent-share-mobile'].value;
  } else if (shareMethod === 'email') {
    shareVal = e.target.elements['smart-agent-share-email'].value;
  } else if (shareMethod === 'wa') {
    shareVal = e.target.elements['smart-agent-share-mobile'].value;
  }

  const cartData = Drupal.alshayaSpc.getCartData();

  const postData = {
    cartId: cartData.cart_id,
    type: shareMethod,
    value: shareVal,
  };

  // Post data to drupal api.
  axios.post('rest/v1/share-cart', postData).then(() => {
    dispatchCustomEvent('smartAgentClosePopup', {
      status: true,
    });
    removeFullScreenLoader();
  }).catch(() => {
    // Processing of error here.
    dispatchCustomEvent('smartAgentClosePopup', {
      status: true,
    });
    removeFullScreenLoader();
  });

  return false;
};

const SASharePopup = (props) => {
  const {
    modalOpen,
    closeModal,
    shareByContext,
  } = props;

  const inputField = getInputField(shareByContext);

  return (
    <Popup
      open={modalOpen}
      closeOnEscape={false}
      closeOnDocumentClick={false}
      className="smart-agent-share-modal"
    >
      <>
        <div className={`smart-agent-share-modal-header-region ${shareByContext}`}>
          <SectionTitle>
            {Drupal.t('Share basket with customer')}
          </SectionTitle>
          <a className="close" onClick={() => closeModal()}>
            &times;
          </a>
        </div>
        <div className={`smart-agent-share-modal-content-region ${shareByContext}`}>
          <form
            className="smart-agent-share-form"
            onSubmit={handleSubmit}
          >
            {inputField}
            <input type="hidden" name="agent-share-method" value={shareByContext} />
            <button
              type="submit"
              className="spc-address-form-submit smart-agent-share-submit"
            >
              {Drupal.t('Share')}
            </button>
          </form>
        </div>
      </>
    </Popup>
  );
};

export default SASharePopup;
