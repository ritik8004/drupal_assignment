import {
  getUserAuraStatus,
  getUserAuraTier,
  getPointToPriceRatio,
  getPriceToPointRatio,
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
    email: '',
    mobile: '',
  };

  return auraDetails;
}

/**
 * Utility function to get aura points for given price.
 */
function getPointsForPrice(price) {
  const accrualRatio = getPointToPriceRatio();
  const points = accrualRatio ? (price * accrualRatio) : 0;

  return points;
}

/**
 * Utility function to get price/currency for given aura points.
 */
function getPriceForPoints(points) {
  const redemptionRatio = getPriceToPointRatio();
  const price = redemptionRatio ? (points / redemptionRatio) : 0;

  return price;
}

export {
  getElementValue,
  showError,
  removeError,
  getAuraLocalStorageKey,
  getAuraDetailsDefaultState,
  getPointsForPrice,
  getPriceForPoints,
};
