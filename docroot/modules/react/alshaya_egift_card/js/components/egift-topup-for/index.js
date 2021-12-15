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
    };
  }

  handleChange = (e) => {
    const eGiftFor = e.target.value;
    const email = 'avinash.shukla@acquia.com';
    if (eGiftFor === 'my card') {
      const result = callMagentoApi(`/V1/egiftcard/hps-search/email/${email}`, 'GET', {});
      if (result instanceof Promise) {
        result.then((response) => {
          if (typeof response.data !== 'undefined' && typeof response.data.error === 'undefined') {
            this.setState({
              userCard: response.data,
              wait: true,
            });
          }
        });
      }
    } else {
      this.setState({
        userCard: null,
      });
    }
  }

  render() {
    const { wait, userCard } = this.state;
    const cardNumber = (userCard !== null) ? userCard.card_number : '';
    const responseType = (userCard !== null) ? userCard.response_type : null;
    const responseMessage = (userCard !== null) ? userCard.response_message : null;

    return (
      <div>
        <ConditionalView condition={isUserAuthenticated() === true}>
          <div
            className="egift-for-field"
            onChange={(e) => this.handleChange(e)}
          >
            <label>
              {Drupal.t('Top-up for', {}, { context: 'egift' })}
              <input
                type="radio"
                name="egift-for"
                value="my card"
              />
              {Drupal.t('My Card', {}, { context: 'egift' })}
              <input
                type="radio"
                name="egift-for"
                value="others card"
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
            <ConditionalView condition={wait === true && responseType === false}>
              { responseType === false && <div className="error">{responseMessage}</div>}
            </ConditionalView>
          </div>
        </ConditionalView>
        <ConditionalView condition={isUserAuthenticated() === false}>
          {Drupal.t('Card Details', {}, { context: 'egift' })}
        </ConditionalView>
        <ConditionalView condition={userCard === null}>
          <div className="egift-card-number-wrapper">
            <input
              type="number"
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
