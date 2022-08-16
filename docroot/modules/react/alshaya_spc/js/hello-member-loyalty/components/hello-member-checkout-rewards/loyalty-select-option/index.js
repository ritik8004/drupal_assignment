import React from 'react';
import { getLoyaltySelectText } from '../../../../../../alshaya_hello_member/js/src/utilities';
import AuraLoyalty from '../aura/aura-loyalty';
import AuraPointsToEarn from '../aura/aura-points-to-earn';

const LoyaltySelectOption = ({
  animationDelay,
  currentOption,
  optionName,
  showLoyaltyPopup,
  helloMemberPoints,
  cart,
}) => (
  <>
    <div className={`loyalty-option ${optionName} fadeInUp`} style={{ animationDelay }} onClick={() => showLoyaltyPopup(optionName)}>
      <input id={`loyalty-option-${optionName}`} defaultChecked={currentOption === optionName} value={optionName} name="loyalty-option" type="radio" className={currentOption === optionName ? 'loyalty-option-selected' : ''} />
      <label className="radio-sim radio-label">
        {(currentOption !== 'aura' || optionName === 'hello_member')
        && <div className="loaylty-option-text">{getLoyaltySelectText(optionName, helloMemberPoints)}</div>}
        {(currentOption === 'aura' && optionName === 'aura')
          && (
            <AuraLoyalty
              open
              cart={cart}
              optionName={optionName}
            />
          )}
      </label>
    </div>
    {(currentOption === 'aura' && optionName === 'aura')
    && (
    <div className="aura-earned-points">
      <AuraPointsToEarn cart={cart} />
    </div>
    )}
  </>
);

export default LoyaltySelectOption;
