/**
 * Get email address of current user.
 *
 * @returns {email}
 */
export function getCurrentUserEmail() {
  const email = drupalSettings.user.user_email;
  return email;
}

export default {
  getCurrentUserEmail,
};
