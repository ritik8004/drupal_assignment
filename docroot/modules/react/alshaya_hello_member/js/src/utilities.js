import React from 'react';
import parse from 'html-react-parser';
import { renderToString } from 'react-dom/server';
import AuraHeaderIcon from '../../../alshaya_aura_react/js/svg-component/aura-header-icon';
import HelloMemberSvg from '../../../alshaya_spc/js/svg-component/hello-member-svg';
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
 * Utility function to get hello member points for given price.
 */
const getPriceToHelloMemberPoint = (price, dictionaryData) => {
  if (hasValue(dictionaryData) && hasValue(dictionaryData.items)) {
    const accrualRatio = dictionaryData.items[0];
    const points = accrualRatio.value ? (price * parseFloat(accrualRatio.value)) : 0;
    return Math.floor(points);
  }
  return null;
};

/**
 * Search for specified element from array.
 */
// eslint-disable-next-line
const findArrayElement = (array, code) => array.find((element) => element.code === code);

const getLoyaltySelectText = (optionName, helloMemberPoints) => {
  if (optionName === 'hello_member') {
    return parse(parse(Drupal.t('@hm_icon Member earns @points points', {
      '@hm_icon': `<span class="hello-member-svg">${renderToString(<HelloMemberSvg />)}</span>`,
      '@points': helloMemberPoints,
    }, { context: 'hello_member' })));
  }
  if (optionName === 'aura') {
    return parse(parse(Drupal.t('Earn/Redeem @aura_icon Points', {
      '@aura_icon': `<span class="hello-member-aura">${renderToString(<AuraHeaderIcon />)}</span>`,
    }, { context: 'hello_member' })));
  }
  return null;
};

export {
  getFormatedMemberId,
  getPointstHistoryPageSize,
  getPriceToHelloMemberPoint,
  findArrayElement,
  setupAccordionHeight,
  getLoyaltySelectText,
};
