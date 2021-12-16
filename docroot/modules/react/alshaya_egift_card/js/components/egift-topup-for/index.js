import React from 'react';
import { isUserAuthenticated } from '../../../../js/utilities/helper';
import ConditionalView
  from '../../../../js/utilities/components/conditional-view';
import { callMagentoApi } from '../../../../js/utilities/requestHelper';

export default class EgiftTopupFor extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      wait: false, // Wait for the API call for linked card details.
      userCard: null, // User linked card data.
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
            userCard: response.data,
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
      this.getUserLinkedCard();
    } else {
      this.setState({
        optionGiftForSelf: false,
        wait: false,
      });
    }
  };

  render() {
    const { wait, userCard, optionGiftForSelf } = this.state;
    const cardNumber = (userCard !== null) ? userCard.card_number : '';
    const responseType = (userCard !== null) ? userCard.response_type : null;

    return (
      <div>
        <ConditionalView condition={isUserAuthenticated() === true && userCard !== null}>
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
          <div className="card-details">
            <ConditionalView condition={wait === true && responseType === true}>
              <span className="egift-linked-card-balance">{Drupal.t('Card Balance: ', {}, { context: 'egift' })}</span>
              <span className="egift-linked-card-balance">
                {
                  Drupal.t('Card No: @cardNo', { '@cardNo': cardNumber !== null ? cardNumber : '' }, { context: 'egift' })
                }
              </span>
              <input
                type="hidden"
                id="card_number"
                name="card_number"
                value={cardNumber !== null ? cardNumber : ''}
              />
            </ConditionalView>
          </div>
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
