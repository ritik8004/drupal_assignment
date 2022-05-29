/**
 * Get the memberId with required format.
 */
const getFormatedMemberId = (memberId) => {
  return memberId.replace(/(\d{4})(\d{4})(\d{4})(\d{4})/, '$1 $2 $3 $4');
};

/**
 * Get the full name.
 */
const getFullName = (firsName, lastName) => {
  return Drupal.t('Hi') + " " + firsName + " " + lastName;
};

export {
  getFormatedMemberId,
  getFullName
};
