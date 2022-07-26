/**
 * Helper function to check if cod mobile verification is enabled.
 */
export const isCodMobileVerifyEnabled = () => drupalSettings.codMobileVerification || false;

export default isCodMobileVerifyEnabled();
