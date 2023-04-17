import { hasValue } from './conditionsUtility';
import isAuraEnabled from './helper';

/**
 * Provides the current currency code.
 *
 * @return null|string
 *   The currency code if present or null.
 */
export default function getCurrencyCode() {
  const alshayaSpc = drupalSettings.alshaya_spc;
  if (Object.prototype.hasOwnProperty.call(alshayaSpc, 'currency_config')) {
    return alshayaSpc.currency_config.currency_code;
  }

  return null;
}

/**
 * Helper function to check if egift card is enabled.
 */
export const isEgiftCardEnabled = () => {
  let egiftCardStatus = false;
  if (typeof drupalSettings.egiftCard !== 'undefined'
    && typeof drupalSettings.egiftCard.enabled !== 'undefined') {
    egiftCardStatus = drupalSettings.egiftCard.enabled;
  }

  return egiftCardStatus;
};

/**
 * Helper function to check if egift card refund is enabled.
 */
export const isEgiftRefundEnabled = () => {
  if (hasValue(drupalSettings.egiftCardRefund)
    && hasValue(drupalSettings.egiftCardRefund.enabled)) {
    return drupalSettings.egiftCardRefund.enabled;
  }

  return false;
};

/**
 * Helper function to check if the order is made through multiple
 * payment methods (for ex. visa + eGift) i.e. hybrid or not.
 */
export const isHybridPayment = (paymentDetails) => {
  if (hasValue(paymentDetails) && Object.keys(paymentDetails).length > 1) {
    return true;
  }

  return false;
};

/**
 * Helper function to get list of not supported payment methods for eGift card refund.
 */
export const getNotSupportedEgiftMethodsForOnlineReturns = () => {
  if (hasValue(drupalSettings.egiftCardRefund)
    && hasValue(drupalSettings.egiftCardRefund.notSupportedEgiftRefundPaymentMethods)) {
    return drupalSettings.egiftCardRefund.notSupportedEgiftRefundPaymentMethods;
  }

  return [];
};

/*
 * Checks if full payment is done by egift and Aura.
 *
 * @param {object} cart
 *   The cart object.
 *
 * @return {boolean}
 *   Returns true if full payment is done by egift and Aura else false.
 */
export const isFullPaymentDoneByPseudoPaymentMedthods = (cart) => {
  // Return false if Egift and Aura is not enabled.
  if (!(isEgiftCardEnabled() && isAuraEnabled())) {
    return false;
  }
  // Extract the redeem information from total.
  const { paidWithAura, egiftRedeemedAmount, balancePayable } = cart.totals;

  // If paid with Aura, egift redeem amount exists in total and balance payable
  // is less than 0 then this confirms that full payment is done by Aura and
  // egift.
  if (hasValue(paidWithAura)
    && hasValue(egiftRedeemedAmount)
    && Object.prototype.hasOwnProperty.call(cart.totals, 'balancePayable')
    && balancePayable <= 0) {
    return true;
  }

  return false;
};

/*
 * Creates required array of shoe sizes.
 *
 * @param {object} configurables
 *   The configurables object.
 *
 * @return {array}
 *   Returns array of shoe sizes ex."36.5,37.5,38,38.5,39,39.5,40" or an empty array.
 */
export const getShoeSize = (configurables) => {
  let shoeSizes = [];
  (shoeSizes) = configurables.map((el) => Object.values(Object.fromEntries(
    Object.entries(el).map(([k, v]) => [k, Object.values(v)[0]]),
  )).toString());
  return shoeSizes;
};

/*
 * Helper function for check status of shoeai.
 *
 * @param array shoeai
 *   The array of shoeai drupalSettings.
 *
 * @return boolean
 *   Returns true or false.
 */
export const getShoeAiStatus = () => {
  const shoeAi = drupalSettings.shoeai;
  if (shoeAi && shoeAi.status !== null && shoeAi.status === 1) {
    return true;
  }
  return false;
};
