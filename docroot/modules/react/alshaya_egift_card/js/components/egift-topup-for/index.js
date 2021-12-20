import React from 'react';
import { isUserAuthenticated } from '../../../../js/utilities/helper';
import ConditionalView
  from '../../../../js/utilities/components/conditional-view';
import PriceElement
  from '../../../../js/utilities/components/price/price-element';

export default class EgiftTopupFor extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      optionGiftForSelf: true, // By default eGift for self is checked.
    };
  }

  /**
   * Select option self or other for top-up card.
   */
  handleChange = (e) => {
    const eGiftFor = e.target.value;
    this.setState({
      optionGiftForSelf: (eGiftFor === 'self'),
    });
  };

  render() {
    const {
      optionGiftForSelf,
    } = this.state;

    const {
      linkedCardNumber,
      linkedCardBalance,
    } = this.props;

    return (
      <div>
        <ConditionalView condition={isUserAuthenticated() === true && linkedCardNumber !== null}>
          <div
            className="egift-for-field"
            onChange={(e) => this.handleChange(e)}
          >
            <label>
              {Drupal.t('Top-up for', {}, { context: 'egift' })}
              <input
                defaultChecked={optionGiftForSelf}
                type="radio"
                name="egift-for"
                value="self"
              />
              {Drupal.t('My Card', {}, { context: 'egift' })}
              <input
                type="radio"
                name="egift-for"
                value="other"
              />
              {Drupal.t('Other\'s Card', {}, { context: 'egift' })}
            </label>
          </div>
          <ConditionalView condition={optionGiftForSelf === true}>
            <div className="card-details">
              <span className="egift-linked-card-balance-label">
                {Drupal.t('Card Balance: ', {}, { context: 'egift' })}
              </span>
              <PriceElement
                amount={linkedCardBalance !== null ? parseFloat(linkedCardBalance) : undefined}
              />
              <span className="egift-linked-card-balance">
                {Drupal.t('Card No:', {}, { context: 'egift' })}
              </span>
              <span>{ linkedCardNumber !== null ? linkedCardNumber : '' }</span>
            </div>
          </ConditionalView>
        </ConditionalView>
        <ConditionalView condition={isUserAuthenticated() === false}>
          {Drupal.t('Card Details', {}, { context: 'egift' })}
        </ConditionalView>
        <ConditionalView condition={linkedCardNumber === null || optionGiftForSelf === false}>
          <div className="egift-card-number-wrapper">
            <input
              type="text"
              id="card_number"
              name="card_number"
              placeholder={Drupal.t('eGift Card number', {}, { context: 'egift' })}
              required
            />
          </div>
        </ConditionalView>
      </div>
    );
  }
}
