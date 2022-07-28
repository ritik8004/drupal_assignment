/**
 * Helper function to check if cod mobile verification is enabled.
 */
export const isCodMobileVerifyEnabled = () => drupalSettings.codMobileVerification || true;

export const getOtpLength = () => 4;

export default {
  isCodMobileVerifyEnabled,
  getOtpLength,
};
