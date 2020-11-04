import { getStorageInfo } from '../../../js/utilities/storage';
import {
  getUserDetails,
  getUserAuraStatus,
  getUserAuraTier,
} from './helper';

/**
 * Utility function to get element value.
 */
function getElementValue(elementId) {
  const elementValue = document.getElementById(elementId)
    ? document.getElementById(elementId).value
    : '';
  return elementValue;
}

/**
 * Utility function to show inline errors in form.
 */
function showError(elementId, msg) {
  const element = document.getElementById(elementId);
  if (element) {
    element.innerHTML = msg;
    element.classList.add('error');
  }
}

/**
 * Utility function to remove inline errors in form.
 */
function removeError(elementId) {
  const element = document.getElementById(elementId);
  if (element) {
    element.innerHTML = '';
    element.classList.remove('error');
  }
}

/**
 * Utility function to get aura localStorage key.
 */
function getAuraLocalStorageKey() {
  return 'aura_data';
}

/**
 * Utility function to get aura details default state.
 */
function getAuraDetailsDefaultState() {
  const auraDetails = {
    loyaltyStatus: getUserAuraStatus(),
    tier: getUserAuraTier(),
    points: 0,
    cardNumber: '',
    pointsOnHold: 0,
    upgradeMsg: '',
    expiringPoints: 0,
    expiryDate: '',
  };

  return auraDetails;
}

export {
  getElementValue,
  showError,
  removeError,
  getAuraLocalStorageKey,
  getAuraDetailsDefaultState,
};
