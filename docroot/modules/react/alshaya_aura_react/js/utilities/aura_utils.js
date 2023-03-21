import { hasValue } from '../../../js/utilities/conditionsUtility';
import { callDrupalApi } from '../../../js/utilities/requestHelper';
import {
  getPointToPriceRatio,
  getPriceToPointRatio,
  getAuraConfig,
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
 * Utility function to get aura localStorage key for checkout.
 */
function getAuraCheckoutLocalStorageKey() {
  return 'aura_checkout_data';
}

/**
 * Utility function to get aura details default state.
 */
function getAuraDetailsDefaultState() {
  const auraDetails = {
    loyaltyStatus: 0,
    tier: 'Tier1',
    points: 0,
    cardNumber: '',
    pointsOnHold: 0,
    upgradeMsg: '',
    expiringPoints: 0,
    expiryDate: '',
    email: '',
    mobile: '',
    firstName: '',
    lastName: '',
    auraPointsToEarn: 0,
  };

  return auraDetails;
}

/**
 * Utility function to get aura points for given price.
 */
function getPriceToPoint(price) {
  const accrualRatio = getPriceToPointRatio();
  const points = accrualRatio ? (price * accrualRatio) : 0;

  return Math.round(points);
}

/**
 * Utility function to get price/currency for given aura points.
 */
function getPointToPrice(points) {
  const redemptionRatio = getPointToPriceRatio();
  const price = redemptionRatio ? (points / redemptionRatio) : 0;

  return price;
}

/**
 * Utility function to process mobile country code.
 */
function getProcessedMobileCountryCode() {
  const processedCountryCodes = [];
  const { phonePrefixList } = getAuraConfig();

  if (hasValue(phonePrefixList)) {
    phonePrefixList.forEach((code) => {
      processedCountryCodes.push({ value: code.replace('+', ''), label: code });
    });
  }

  return processedCountryCodes;
}

/**
 * Utility function to add inline loader.
 */
function addInlineLoader(selector) {
  const element = document.querySelectorAll(selector);

  if (element.length > 0) {
    element.forEach((el) => {
      el.classList.add('loading');
    });
  }
}

/**
 * Utility function to hide inline loader.
 */
function removeInlineLoader(selector) {
  const element = document.querySelectorAll(selector);

  if (element.length > 0) {
    element.forEach((el) => {
      el.classList.remove('loading');
    });
  }
}

/**
 * Utility function to show inline error.
 */
function showInlineError(selector, msg) {
  const element = document.querySelectorAll(selector);

  if (element.length > 0) {
    element.forEach((el) => {
      const e = el;
      e.innerHTML = msg;
      e.classList.add('error');
    });
  }
}

/**
 * Utility function to hide inline error.
 */
function removeInlineError(selector) {
  const element = document.querySelectorAll(selector);

  if (element.length > 0) {
    element.forEach((el) => {
      const e = el;
      e.innerHTML = '';
      e.classList.remove('error');
    });
  }
}

/**
 * Utility function to get not you label.
 */
function getNotYouLabel(notYouFailed) {
  const label = (notYouFailed) ? Drupal.t('Try again') : Drupal.t('Not you?');

  return label;
}

/**
 * Utility function to get tooltip msg for points on hold.
 */
function getTooltipPointsOnHoldMsg() {
  return Drupal.t('Your points will be credited to your Aura account. You will be able to redeem these a day after your order is delivered.', {}, { context: 'aura' });
}

/**
 * Utility function to get aura context.
 */
function isMyAuraContext() {
  if (hasValue(drupalSettings.aura.context)
    && drupalSettings.aura.context === 'my_aura') {
    return true;
  }

  return false;
}

/**
 * Utility function to get the static HTML of AURA landing page.
 *
 * @returns {object|boolean}
 *   The address book info.
 */
const getStaticHtmlOfAuraLanding = async () => {
  const response = await callDrupalApi('/rest/v1/page/simple?url=aura-info', 'GET');
  if (hasValue(response)
    && hasValue(response.data)) {
    return response.data;
  }
  return false;
};

/**
 * Utility function to get the static HTML of AURA landing page.
 *
 * @returns {object|boolean}
 *   The aura landing page content.
 */
const getLoyaltyPageContent = async () => {
  let staticPageUrl = '';
  if (hasValue(drupalSettings.aura)
    && hasValue(drupalSettings.aura.loyaltyStaticPageUrl)) {
    staticPageUrl = drupalSettings.aura.loyaltyStaticPageUrl;
  }
  // Call API only if the static page url is available.
  if (staticPageUrl) {
    const response = await callDrupalApi(`/rest/v1/page/simple?url=${staticPageUrl}`, 'GET');
    if (hasValue(response)
      && hasValue(response.data)) {
      return response.data;
    }
  }

  return false;
};

export {
  getElementValue,
  showError,
  removeError,
  getAuraLocalStorageKey,
  getAuraDetailsDefaultState,
  getPriceToPoint,
  getPointToPrice,
  getProcessedMobileCountryCode,
  addInlineLoader,
  removeInlineLoader,
  showInlineError,
  removeInlineError,
  getNotYouLabel,
  getAuraCheckoutLocalStorageKey,
  getTooltipPointsOnHoldMsg,
  isMyAuraContext,
  getStaticHtmlOfAuraLanding,
  getLoyaltyPageContent,
};
