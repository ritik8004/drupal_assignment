import { hasValue } from '../../../js/utilities/conditionsUtility';

/**
 * Set up accordion container height.
 */
const setupAccordionHeight = (ref) => {
  if (ref.current !== null) {
    const element = ref.current;
    element.style.maxHeight = `${ref.current.offsetHeight}px`;
  }
};

export default setupAccordionHeight;

/**
 * Update the memberId with required format (4343 6443 6554 2322).
 */
const getFormatedMemberId = (memberId) => memberId.replace(/(\d{4})(\d{4})(\d{4})(\d{4})/, '$1 $2 $3 $4');

/**
 * Utility function to get points history page size from config.
 * Default value we are keeping as 10,
 */
const getPointstHistoryPageSize = () => (drupalSettings.pointsHistoryPageSize
  ? drupalSettings.pointsHistoryPageSize : 10);

/**
 * Utility function to get hello member points for given price.
 */
const getPriceToHelloMemberPoint = (price, dictionaryData) => {
  if (hasValue(dictionaryData) && hasValue(dictionaryData.items)) {
    const accrualRatio = dictionaryData.items[0];
    const points = accrualRatio.value ? (price * parseFloat(accrualRatio.value)) : 0;
    return Math.round(points);
  }
  return null;
};

/**
 * Utility function to get hello member points for given price.
 */
const getLoyaltyOptionText = (key) => {
  if (key === 'hello_member_loyalty') {
    return Drupal.t('H&M membership', {}, { context: 'hello_member' });
  } if (key === 'aura_loyalty') {
    return Drupal.t('Aura', {}, { context: 'hello_member' });
  }
  return null;
};

export {
  getFormatedMemberId,
  getPointstHistoryPageSize,
  getPriceToHelloMemberPoint,
  getLoyaltyOptionText,
};
