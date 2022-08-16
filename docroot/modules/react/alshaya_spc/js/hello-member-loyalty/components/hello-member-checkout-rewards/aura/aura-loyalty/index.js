import React from 'react';
import Collapsible from 'react-collapsible';
import { getLoyaltySelectText } from '../../../../../../../alshaya_hello_member/js/src/utilities';
import AuraLoyaltyForm from '../aura-loyalty-form';

/**
 * Display the aura loyalty form trigger component.
 * On click of this component, aura loyalty form div will open.
 */
const auraLoyaltyHeader = (optionName, helloMemberPoints) => (
  <div className="loaylty-option-text">{getLoyaltySelectText(optionName, helloMemberPoints)}</div>
);

const AuraLoyalty = ({
  optionName,
  helloMemberPoints,
  cart,
  open,
}) => (
  <>
    <Collapsible
      trigger={auraLoyaltyHeader(optionName, helloMemberPoints)}
      open={open}
    >
      <div className="spc-aura-link-card-form active">
        <AuraLoyaltyForm
          cart={cart}
        />
      </div>
    </Collapsible>
  </>
);

export default AuraLoyalty;
