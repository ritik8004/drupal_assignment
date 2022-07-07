import React from 'react';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import { getAuraRedeemText, getHelloMemberTextForGuestUser } from '../utilities/loyalty-options-helper';

const GuestUserLoyalty = ({
  helloMemberPoints,
  animationDelay,
}) => {
  if (!hasValue(helloMemberPoints)) {
    return null;
  }
  return (
    <div className="loyalty-options-guest">
      <div className="loyalty-option hello-member-loyalty fadeInUp" style={{ animationDelay }}>
        <div className="loaylty-option-text">{getHelloMemberTextForGuestUser(helloMemberPoints)}</div>
      </div>
      <div className="loyalty-option aura-loyalty fadeInUp" style={{ animationDelay }}>
        <div className="loaylty-option-text">
          {getAuraRedeemText()}
        </div>
      </div>
    </div>
  );
};

export default GuestUserLoyalty;
