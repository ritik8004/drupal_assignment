import React from 'react';
import { getAuraRedeemText, getHelloMemberTextForRegisteredUser } from '../utilities/loyalty-options-helper';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';

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
        <div className="loyalty-option hello-member-loyalty fadeInUp" style={{ animationDelay }} onClick={() => this.changeLoyaltyOption('hello_member_loyalty')}>
          <input id="loyalty-option-hello_member_loyalty" defaultChecked={selectedOption === 'hello_member_loyalty'} value="hello_member_loyalty" name="loyalty-option" type="radio" />
          <label className="radio-sim radio-label">
            <div className="loaylty-option-text">{getHelloMemberTextForRegisteredUser(helloMemberPoints)}</div>
          </label>
        </div>
        <div className="loyalty-option aura-loyalty fadeInUp" style={{ animationDelay }} onClick={() => this.changeLoyaltyOption('aura_loyalty')}>
          <input id="loyalty-option-aura_loyalty" defaultChecked={selectedOption === 'aura_loyalty'} value="aura_loyalty" name="loyalty-option" type="radio" />
          <label className="radio-sim radio-label">
            <div className="loaylty-option-text">
              {getAuraRedeemText()}
            </div>
          </label>
        </div>
      </div>
    );
  }
}

export default RegisteredUserLoyalty;
