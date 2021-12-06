import React from 'react';
import {
  getQueryStringForEgiftCards,
} from '../../utilities';
import ConditionalView
  from '../../../../js/utilities/components/conditional-view';
import EgiftCardsListStepOne from '../egifts-card-step-one';
import EgiftCardStepTwo from '../egift-card-step-two';
import { callMagentoApi } from '../../../../js/utilities/requestHelper';

export default class EgiftCardPurchase extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      egiftItems: null,
      wait: false, // Waiting till we get data from api and show to user.
      activateStepTwo: false, // Set on amount select to show step 2.
      amountSet: 0,
    };
  }

  async componentDidMount() {
    const params = getQueryStringForEgiftCards();
    const response = await callMagentoApi('/V1/products', 'GET', params);
    if (typeof response.data !== 'undefined' && typeof response.data.error === 'undefined') {
      this.setState({
        egiftItems: response.data.items,
      });
    }
    this.setState({
      wait: true,
    });
  }

  /**
   * Show next step fields when user select amount.
   */
  handleAmountSelect = (activate, amount) => {
    this.setState({
      activateStepTwo: activate,
      amountSet: amount,
    });
  }

  render() {
    const {
      egiftItems,
      wait,
      activateStepTwo,
      amountSet,
    } = this.state;

    return (
      <>
        <ConditionalView condition={egiftItems === null && wait === true}>
          <div>
            <p>{Drupal.t('No eGift cards found.', {}, { context: 'egift' })}</p>
          </div>
        </ConditionalView>
        <ConditionalView condition={egiftItems !== null}>
          <div className="egifts-form-wrap">
            <form onSubmit={this.handleSubmit}>
              <EgiftCardsListStepOne
                items={egiftItems}
                handleEgiftSelect={this.handleEgiftSelect}
                handleAmountSelect={this.handleAmountSelect}
              />
              <EgiftCardStepTwo activate={activateStepTwo} />
              <div className="action-buttons">
                <button type="submit" name="add-to-cart" className="btn">
                  {Drupal.t('add to bag', {}, { context: 'egift' })}
                </button>
                <button type="submit" name="checkout" className="btn">
                  {Drupal.t('checkout', {}, { context: 'egift' })}
                </button>
              </div>
              <input type="hidden" name="egift-amount" value={amountSet} />
            </form>
          </div>
        </ConditionalView>
      </>
    );
  }
}
