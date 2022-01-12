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

  handleEvent = (e) => {
    if (e.currentTarget.value.length > 0) {
      e.currentTarget.classList.add('focus');
    } else {
      e.currentTarget.classList.remove('focus');
    }
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
      <>
        <ConditionalView condition={isUserAuthenticated() === true && linkedCardNumber !== null}>
          <div className="egift-for-field">
            <div className="egift-purchase-input-title subtitle-text">
              {Drupal.t('Top-up for', {}, { context: 'egift' })}
            </div>
            <div className="egift-input-field-wrapper">
              <div className="egift-input-field-item">
                <input
                  defaultChecked={optionGiftForSelf}
                  type="radio"
                  name="egift-for"
                  id="egiftFor-self"
                  value="self"
                  onChange={(e) => this.handleChange(e)}
                />
                <label htmlFor="egiftFor-self">
                  {Drupal.t('My Card', {}, { context: 'egift' })}
                </label>
              </div>
              <div className="egift-input-field-item">
                <input
                  type="radio"
                  name="egift-for"
                  id="egiftFor-other"
                  value="other"
                  onChange={(e) => this.handleChange(e)}
                />
                <label htmlFor="egiftFor-other">
                  {Drupal.t('Other\'s Card', {}, { context: 'egift' })}
                </label>
              </div>
            </div>
          </div>
          <ConditionalView condition={optionGiftForSelf === true}>
            <div className="card-details">
              <div className="egift-linked-card-balance">
                <span className="egift-linked-card-balance-label">
                  {Drupal.t('Card Balance: ', {}, { context: 'egift' })}
                </span>
                <PriceElement
                  amount={linkedCardBalance !== null ? parseFloat(linkedCardBalance) : undefined}
                />
              </div>
              <div className="egift-linked-card-balance">
                <span className="egift-linked-card-balance-label">
                  {Drupal.t('Card No:', {}, { context: 'egift' })}
                </span>
                <span>{ linkedCardNumber !== null ? linkedCardNumber : '' }</span>
              </div>
            </div>
          </ConditionalView>
        </ConditionalView>
        <ConditionalView condition={isUserAuthenticated() === false}>
          <div className="card-details-label subtitle-text">
            {Drupal.t('Card Details', {}, { context: 'egift' })}
          </div>
        </ConditionalView>
        <ConditionalView condition={linkedCardNumber === null || optionGiftForSelf === false}>
          <div className="egift-input-textfield-wrapper">
            <div className="egift-input-textfield-item egift-topup-card-number">
              <input
                type="text"
                id="card_number"
                name="card_number"
                onFocus={(e) => this.handleOnFocus(e)}
                onBlur={(e) => this.handleEvent(e)}
              />
              <div className="error" id="card-number-error" />
              <div className="c-input__bar" />
              <label>{Drupal.t('eGift Card Number', {}, { context: 'egift' })}</label>
            </div>
          </div>
        </ConditionalView>
      </>
    );
  }
}
