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
  if (hasValue(drupalSettings.currency_code) && hasValue(dictionaryData)) {
    const accrualRatio = dictionaryData.items.find(
      (item) => item.code === drupalSettings.currency_code,
    );
    if (hasValue(accrualRatio)) {
      const points = accrualRatio.value ? (price * parseFloat(accrualRatio.value)) : 0;
      return Math.round(points);
    }
  }
  return null;
};

export {
  getFormatedMemberId,
  getPointstHistoryPageSize,
  getPriceToHelloMemberPoint,
};
