import React from 'react';
import HeroImage from '../egifts-card-step-one/hero-image';
import EgiftCardAmount from '../egifts-card-step-one/egift-card-amount';
import {
  getParamsForTopUpCardSearch,
} from '../../utilities';
import { callMagentoApi } from '../../../../js/utilities/requestHelper';
import ConditionalView
  from '../../../../js/utilities/components/conditional-view';
import EgiftTopupFor from '../egift-topup-for';

export default class EgiftTopPurchase extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      topUpCard: null,
      wait: false, // Flag to check api call is complete.
      amountSet: 0,
      activate: false,
    };
  }

  async componentDidMount() {
    const params = getParamsForTopUpCardSearch();
    // Get Top up card details.
    const response = await callMagentoApi('/V1/products', 'GET', params);
    if (typeof response.data !== 'undefined' && typeof response.data.error === 'undefined') {
      this.setState({
        topUpCard: response.data.items[0],
        wait: true,
      });
    }
  }

  /**
   * Show next step fields when user select amount.
   */
  handleAmountSelect = (activate, amount) => {
    this.setState({
      amountSet: amount,
      activate: true,
    });
  }

  render() {
    const {
      topUpCard, wait, activate, amountSet,
    } = this.state;

    return (
      <div className="egifts-form-wrap">
        <form onSubmit={this.handleSubmit} className="egift-form">
          <ConditionalView condition={wait === true}>
            <div className="egift-hero-wrap">
              <HeroImage item={topUpCard} />
            </div>
            <div className="egift-topup-fields-wrap">
              <EgiftTopupFor />
              <EgiftCardAmount selected={topUpCard} handleAmountSelect={this.handleAmountSelect} />
              <input type="hidden" name="egift-amount" value={amountSet} />
            </div>
          </ConditionalView>
          <div className="action-buttons">
            <button
              type="submit"
              name="top-up"
              className="btn"
              disabled={!activate}
            >
              {Drupal.t('Top-up', {}, { context: 'egift' })}
            </button>
          </div>
        </form>
      </div>
    );
  }
}
