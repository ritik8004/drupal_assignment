import React from 'react';
import { isUserAuthenticated } from '../../../../js/utilities/helper';
import ConditionalView
  from '../../../../js/utilities/components/conditional-view';
import { callMagentoApi } from '../../../../js/utilities/requestHelper';
import getCurrencyCode from '../../../../js/utilities/util';

export default class EgiftTopupFor extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      wait: false, // Wait for the API call for linked card details.
      linkedCardNumber: null, // User linked card number.
      linkedCardBalance: null, // User linked card balance.
      optionGiftForSelf: true, // By default eGift for self is checked.
    };
  }

  componentDidMount = () => {
    // Get user linked eGift card.
    this.getUserLinkedCard();
  }

  /**
   * Get User linked card helper.
   */
  getUserLinkedCard = () => {
    // Call to get customer linked card details.
    const result = callMagentoApi('/V1/customers/hpsCustomerData', 'GET', {});
    if (result instanceof Promise) {
      result.then((response) => {
        if (typeof response.data !== 'undefined' && typeof response.data.error === 'undefined') {
          this.setState({
            linkedCardNumber: response.data.card_number !== null ? response.data.card_number : null,
            linkedCardBalance:
              response.data.current_balance !== null ? response.data.current_balance : null,
            wait: true,
            optionGiftForSelf: true,
          });
        }
      });
    }
  };

  /**
   * Select option self or other for top-up card.
   */
  handleChange = (e) => {
    const eGiftFor = e.target.value;
    if (eGiftFor === 'self') {
      this.setState({
        optionGiftForSelf: true,
        wait: true,
      });
    } else {
      this.setState({
        optionGiftForSelf: false,
        wait: false,
      });
    }
  };

  render() {
    const {
      wait,
      linkedCardNumber,
      linkedCardBalance,
      optionGiftForSelf,
    } = this.state;

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
          <ConditionalView condition={wait === true}>
            <div className="card-details">
              <span className="egift-linked-card-balance">
                {
                  Drupal.t('Card Balance: @currency @balance', {
                    '@currency': getCurrencyCode(),
                    '@balance': linkedCardBalance !== null ? linkedCardBalance : '',
                  }, { context: 'egift' })
                }
              </span>
              <span className="egift-linked-card-balance">
                {
                  Drupal.t('Card No: @cardNo', { '@cardNo': linkedCardNumber !== null ? linkedCardNumber : '' }, { context: 'egift' })
                }
              </span>
            </div>
          </ConditionalView>
        </ConditionalView>
        <ConditionalView condition={isUserAuthenticated() === false}>
          {Drupal.t('Card Details', {}, { context: 'egift' })}
        </ConditionalView>
        <ConditionalView condition={wait === false}>
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
