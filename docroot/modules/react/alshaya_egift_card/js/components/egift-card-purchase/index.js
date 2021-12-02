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
      flag: false,
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
      flag: true,
    });
  }

  render() {
    const { egiftItems, flag } = this.state;

    return (
      <>
        <ConditionalView condition={egiftItems !== null}>
          <div className="egifts-form-wrap">
            <form onSubmit={this.handleSubmit}>
              <EgiftCardsListStepOne
                items={egiftItems}
                handleEgiftSelect={this.handleEgiftSelect}
              />
              <EgiftCardStepTwo />
              <div className="action-buttons">
                <button type="submit" name="add-to-cart" className="btn">
                  {Drupal.t('add to bag')}
                </button>
                <button type="submit" name="checkout" className="btn">
                  {Drupal.t('checkout')}
                </button>
              </div>
            </form>
          </div>
        </ConditionalView>
        <ConditionalView condition={egiftItems === null && flag === true}>
          <div>
            <p>{Drupal.t('Not Found.')}</p>
          </div>
        </ConditionalView>
      </>
    );
  }
}
