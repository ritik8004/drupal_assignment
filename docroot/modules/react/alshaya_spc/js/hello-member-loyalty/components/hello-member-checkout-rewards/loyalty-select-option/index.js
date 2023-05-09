import React from 'react';
import { getLoyaltySelectText } from '../utilities/loyalty_helper';
import AuraLoyalty from '../aura/aura-loyalty';
import AuraPointsToEarn from '../aura/aura-points-to-earn';
import ToolTip from '../../../../utilities/tooltip';
import getStringMessage from '../../../../utilities/strings';

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
        && <div className="loyalty-option-text">{getLoyaltySelectText(optionName, helloMemberPoints)}</div>}
        {(optionName === 'hello_member') && (<ToolTip enable>{getStringMessage('hello_member_points_tooltip')}</ToolTip>)}
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
      <AuraPointsToEarn />
    </div>
    )}
  </>
);

export default LoyaltySelectOption;
