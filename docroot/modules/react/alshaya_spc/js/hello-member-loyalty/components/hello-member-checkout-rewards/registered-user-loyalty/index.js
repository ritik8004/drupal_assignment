import React from 'react';
import parse from 'html-react-parser';
import { renderToString } from 'react-dom/server';
import HelloMemberSvg from '../../../../svg-component/hello-member-svg';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import LoyaltySelectOption from '../loyalty-select-option';
import { isAuraIntegrationEnabled } from '../../../../../../js/utilities/helloMemberHelper';
import ConditionalView from '../../../../../../js/utilities/components/conditional-view';

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
        <ConditionalView condition={isAuraIntegrationEnabled()}>
          <LoyaltySelectOption
            animationDelay={animationDelay}
            selectedOption={selectedOption}
            optionName="hello_member_loyalty"
            changeLoyaltyOption={this.changeLoyaltyOption}
            helloMemberPoints={helloMemberPoints}
          />
          <LoyaltySelectOption
            animationDelay={animationDelay}
            selectedOption={selectedOption}
            optionName="aura_loyalty"
            changeLoyaltyOption={this.changeLoyaltyOption}
            helloMemberPoints={helloMemberPoints}
          />
        </ConditionalView>
        <ConditionalView condition={!isAuraIntegrationEnabled()}>
          <div className="loyalty-option hello-member-loyalty fadeInUp" style={{ animationDelay }}>
            <div className="loaylty-option-text">
              {parse(parse(Drupal.t('@hm_icon Member earns @points points', {
                '@hm_icon': `<span class="hello-member-svg">${renderToString(<HelloMemberSvg />)}</span>`,
                '@points': helloMemberPoints,
              })))}
            </div>
          </div>
        </ConditionalView>
      </div>
    );
  }
}

export default RegisteredUserLoyalty;
