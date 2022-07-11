import React from 'react';
import { getAuraRedeemText, getHelloMemberTextForRegisteredUser } from '../utilities/loyalty-options-helper';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import LoyaltySelectOption from '../loyalty-select-option';

class RegisteredUserLoyalty extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      selectedOption: 'hello_member_loyalty',
    };
  }

  /**
   * Handle change in loyalty options by customer.
   *
   * @param {string} method
   *  Selected method by customer.
   */
  changeLoyaltyOption = (method) => {
    // @todo: Trigger a pop-up to confirm the loyalty option.
    // @todo: Refresh cart with the selected value.
    this.setState({
      selectedOption: method,
    });
  }

  render() {
    const { animationDelay, helloMemberPoints } = this.props;
    const { selectedOption } = this.state;

    if (!hasValue(helloMemberPoints)) {
      return null;
    }

    return (
      <div className="loyalty-options-registered">
        <LoyaltySelectOption
          animationDelay={animationDelay}
          selectedOption={selectedOption}
          optionName="hello_member_loyalty"
          changeLoyaltyOption={this.changeLoyaltyOption}
          optionText={getHelloMemberTextForRegisteredUser(helloMemberPoints)}
        />
        <LoyaltySelectOption
          animationDelay={animationDelay}
          selectedOption={selectedOption}
          optionName="aura_loyalty"
          changeLoyaltyOption={this.changeLoyaltyOption}
          optionText={getAuraRedeemText()}
        />
      </div>
    );
  }
}

export default RegisteredUserLoyalty;
