import React from 'react';
import parse from 'html-react-parser';
import { renderToString } from 'react-dom/server';
import HelloMemberSvg from '../../../../svg-component/hello-member-svg';
import AuraHeaderIcon from '../../../../../../alshaya_aura_react/js/svg-component/aura-header-icon';

const getLoyaltySelectText = (optionName, helloMemberPoints) => {
  if (optionName === 'hello_member_loyalty') {
    return parse(parse(Drupal.t('@hm_icon Member earns @points points', {
      '@hm_icon': `<span class="hello-member-svg">${renderToString(<HelloMemberSvg />)}</span>`,
      '@points': helloMemberPoints,
    })));
  }
  if (optionName === 'aura_loyalty') {
    return parse(parse(Drupal.t('Earn/Redeem @aura_icon Points', {
      '@aura_icon': `<span class="hello-member-aura">${renderToString(<AuraHeaderIcon />)}</span>`,
    })));
  }
  return null;
};

const LoyaltySelectOption = ({
  animationDelay,
  selectedOption,
  optionName,
  changeLoyaltyOption,
  helloMemberPoints,
}) => (
  <div className={`loyalty-option ${optionName} fadeInUp`} style={{ animationDelay }} onClick={() => changeLoyaltyOption(optionName)}>
    <input id={`loyalty-option-${optionName}`} defaultChecked={selectedOption === optionName} value={optionName} name="loyalty-option" type="radio" />
    <label className="radio-sim radio-label">
      <div className="loaylty-option-text">{getLoyaltySelectText(optionName, helloMemberPoints)}</div>
    </label>
  </div>
);

export default LoyaltySelectOption;
