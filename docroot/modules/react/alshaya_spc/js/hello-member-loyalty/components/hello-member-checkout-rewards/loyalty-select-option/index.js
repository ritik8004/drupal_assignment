import React from 'react';
import parse from 'html-react-parser';
import Collapsible from 'react-collapsible';
import { renderToString } from 'react-dom/server';
import HelloMemberSvg from '../../../../svg-component/hello-member-svg';
import AuraHeaderIcon from '../../../../../../alshaya_aura_react/js/svg-component/aura-header-icon';
import ToolTip from '../../../../utilities/tooltip';
import AuraLoyaltyForm from '../aura/aura-loyalty-form';
import getStringMessage from '../../../../../../js/utilities/strings';

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

/**
 * Display the aura loyalty form trigger component.
 * On click of this component, aura loyalty form div will open.
 */
const auraLoyaltyHeader = (optionName, helloMemberPoints) => {
  return (
    <div className="loaylty-option-text">{getLoyaltySelectText(optionName, helloMemberPoints)}</div>
  );
}

const LoyaltySelectOption = ({
  animationDelay,
  currentOption,
  optionName,
  showLoyaltyPopup,
  helloMemberPoints,
}) => (
  <div className={`loyalty-option ${optionName} fadeInUp`} style={{ animationDelay }} onClick={() => showLoyaltyPopup(optionName)}>
    <input id={`loyalty-option-${optionName}`} defaultChecked={currentOption === optionName} value={optionName} name="loyalty-option" type="radio" />
    <label className="radio-sim radio-label">
      {(currentOption !== 'aura_loyalty' || optionName === 'hello_member_loyalty') &&
        <div className="loaylty-option-text">{getLoyaltySelectText(optionName, helloMemberPoints)}</div>
      }
      {(currentOption === 'aura_loyalty' && optionName === 'aura_loyalty') &&
        <Collapsible
          trigger={auraLoyaltyHeader(optionName, helloMemberPoints)}
          open={currentOption === 'aura_loyalty' && optionName === 'aura_loyalty' ? true : false}
        >
          <div className={`spc-aura-link-card-form active`}>
            <AuraLoyaltyForm />
          </div>
        </Collapsible>
      }
    </label>
  </div>
);

export default LoyaltySelectOption;
