import React from 'react';
import parse from 'html-react-parser';
import Collapsible from 'react-collapsible';
import { renderToString } from 'react-dom/server';
import HelloMemberSvg from '../../../../svg-component/hello-member-svg';
import AuraHeaderIcon from '../../../../../../alshaya_aura_react/js/svg-component/aura-header-icon';
import AuraLoyaltyForm from '../aura/aura-loyalty-form';

const getLoyaltySelectText = (optionName, helloMemberPoints) => {
  if (optionName === 'hello_member') {
    return parse(parse(Drupal.t('@hm_icon Member earns @points points', {
      '@hm_icon': `<span class="hello-member-svg">${renderToString(<HelloMemberSvg />)}</span>`,
      '@points': helloMemberPoints,
    })));
  }
  if (optionName === 'aura') {
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
const auraLoyaltyHeader = (optionName, helloMemberPoints) => (
  <div className="loaylty-option-text">{getLoyaltySelectText(optionName, helloMemberPoints)}</div>
);

const LoyaltySelectOption = ({
  animationDelay,
  currentOption,
  optionName,
  showLoyaltyPopup,
  helloMemberPoints,
  loyaltyCard,
  cart,
}) => (
  <div className={`loyalty-option ${optionName} fadeInUp`} style={{ animationDelay }} onClick={() => showLoyaltyPopup(optionName)}>
    <input id={`loyalty-option-${optionName}`} defaultChecked={currentOption === optionName} value={optionName} name="loyalty-option" type="radio" />
    <label className="radio-sim radio-label">
      {(currentOption !== 'aura' || optionName === 'hello_member')
        && <div className="loaylty-option-text">{getLoyaltySelectText(optionName, helloMemberPoints)}</div>}
      {(currentOption === 'aura' && optionName === 'aura')
          && (
          <Collapsible
            trigger={auraLoyaltyHeader(optionName, helloMemberPoints)}
            open
          >
            <div className="spc-aura-link-card-form active">
              <AuraLoyaltyForm
                cart={cart}
                loyaltyCard={loyaltyCard}
              />
            </div>
          </Collapsible>
          )}
    </label>
  </div>
);

export default LoyaltySelectOption;
