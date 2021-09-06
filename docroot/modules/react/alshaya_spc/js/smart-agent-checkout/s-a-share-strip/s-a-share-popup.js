import React from 'react';
import axios from 'axios';
import Popup from 'reactjs-popup';
import SectionTitle from '../../utilities/section-title';
import TextField from '../../utilities/textfield';
import dispatchCustomEvent from '../../../../js/utilities/events';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
  validateInfo,
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

const displayErrorMessage = (id, message) => {
  document.getElementById(id).innerHTML = message;
  document.getElementById(id).classList.add('error');
};

const removeErrorMessage = (id) => {
  document.getElementById(id).innerHTML = '';
  document.getElementById(id).classList.remove('error');
};

const validateNonEmptyInputs = () => {
  // Since we have only one input field.
  const element = document.querySelector('.smart-agent-share-modal-content-region input');
  const name = element.getAttribute('name');

  // Mobile number validation.
  if (name === 'smart-agent-share-mobile') {
    if (element.value.length < 1 || element.value.match(/^[0-9]+$/) === null) {
      displayErrorMessage(`${name}-error`, Drupal.t('Please enter valid mobile number.'));
      return true;
    }
  }
  // Email validation.
  if (name === 'smart-agent-share-email') {
    if (element.value.length < 1) {
      displayErrorMessage(`${name}-error`, Drupal.t('Please enter your email address.'));
      return true;
    }
  }

  removeErrorMessage(`${name}-error`);
  return false;
};

const validateInputValues = () => {
  // Since we have only one input field.
  const element = document.querySelector('.smart-agent-share-modal-content-region input');
  const name = element.getAttribute('name');
  const inputFieldKey = (name === 'smart-agent-share-mobile') ? 'mobile' : 'email';
  const data = { [inputFieldKey]: element.value };
  let hasError = false;
  const validationRequest = validateInfo(data);
  showFullScreenLoader();

  return validationRequest.then((result) => {
    if (result.status === 200 && result.data.status) {
      // If not valid mobile number.
      if (result.data.mobile === false) {
        displayErrorMessage('smart-agent-share-mobile-error', Drupal.t('Please enter valid mobile number.'));
        hasError = true;
      } else if (result.data.email === 'invalid') {
        // If not valid email.
        displayErrorMessage('smart-agent-share-email-error', Drupal.t('Please enter your email address.'));
        hasError = true;
      } else {
        removeErrorMessage(`${name}-error`);
      }
    }
    removeFullScreenLoader();
    return hasError;
  });
};

const handleSubmit = (e) => {
  e.preventDefault();

  // FE validation to check if input is non empty.
  const error = validateNonEmptyInputs();
  if (error) {
    return false;
  }
  const { elements } = e.target;
  showFullScreenLoader();

  const validationRequest = validateInputValues();
  if (validationRequest instanceof Promise) {
    validationRequest.then(async (hasError) => {
      if (hasError === false) {
        // Get the share method type.
        const shareMethod = elements['agent-share-method'].value;
        let shareVal = '';
        if (shareMethod === 'sms') {
          shareVal = elements['smart-agent-share-mobile'].value;
        } else if (shareMethod === 'email') {
          shareVal = elements['smart-agent-share-email'].value;
        } else if (shareMethod === 'wa') {
          shareVal = elements['smart-agent-share-mobile'].value;
        }

        const cartData = await window.commerceBackend.pushAgentDetailsInCart();

        const postData = {
          cartId: cartData.data.cart_id_int || cartData.data.cart_id,
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
      }
    });
  }

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
