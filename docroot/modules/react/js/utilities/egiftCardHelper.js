import { callMagentoApi } from './requestHelper';

/**
 * Helper function to check if egift card is enabled.
 */
export default function isEgiftCardEnabled() {
  let egiftCardStatus = false;
  if (typeof drupalSettings.egiftCard !== 'undefined'
    && typeof drupalSettings.egiftCard.enabled !== 'undefined') {
    egiftCardStatus = drupalSettings.egiftCard.enabled;
  }

  return egiftCardStatus;
}

/**
 * Provides the egift send otp api response.
 *
 * @param {*} egiftCardNumber
 *
 */
export const sendOtp = (egiftCardNumber) => {
  const data = {
    accountInfo: {
      cardNumber: egiftCardNumber,
      action: 'send_otp',
    },
  };
  // Send OTP to get card balance.
  return callMagentoApi('/V1/egiftcard/getBalance', 'POST', data);
};
