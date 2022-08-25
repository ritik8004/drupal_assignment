/**
 * Set up accordion container height.
 */
const setupAccordionHeight = (ref) => {
  if (ref.current !== null) {
    const element = ref.current;
    element.style.maxHeight = `${ref.current.offsetHeight}px`;
  }
};

/**
 * Update the memberId with required format (4343 6443 6554 2322).
 */
const getFormatedMemberId = (memberId) => memberId.replace(/(\d{4})(\d{4})(\d{4})(\d{4})/, '$1 $2 $3 $4');

/**
 * Utility function to get points history page size from config.
 * Default value we are keeping as 10,
 */
const getPointstHistoryPageSize = () => drupalSettings.pointsHistoryPageSize || 10;

/**
 * Search for specified element from array.
 */
// eslint-disable-next-line
const findArrayElement = (array, code) => array.find((element) => element.code === code);

export {
  getFormatedMemberId,
  getPointstHistoryPageSize,
  findArrayElement,
  setupAccordionHeight,
};
