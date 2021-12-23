import React from 'react';
import EgiftCardLinked from './my-account-egift-card-linked';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import EgiftCardNotLinked from './my-account-egift-card-not-linked';
import { callMagentoApi } from '../../../../js/utilities/requestHelper';

class MyEgiftCard extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: true, // Flag to check api call is complete.
      linkedCard: null,
    };
  }

  componentDidMount() {
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
            linkedCard: response.data,
            wait: false,
          });
        }
      });
    } else {
      this.setState({
        wait: false,
      });
    }
  };

  /**
   * Handle remove linked card.
   */
  removeCard = () => {
    this.setState({
      linkedCard: null,
      wait: false,
    });
  }

  /**
   * Shows card after user links the card.
   */
  showCard = () => {
    this.getUserLinkedCard();
  }

  render() {
    const { wait, linkedCard } = this.state;
    // Return if API call for Users linkedCard is not complete.
    if (wait) {
      return null;
    }

    return (
      <>
        <ConditionalView condition={linkedCard !== null}>
          <div className="egift-my-account">
            <EgiftCardLinked linkedCard={linkedCard} removeCard={this.removeCard} />
          </div>
        </ConditionalView>
        <ConditionalView condition={linkedCard === null}>
          <div className="egift-my-account">
            <EgiftCardNotLinked handleCardChange={this.removeCard} showCard={this.showCard} />
          </div>
        </ConditionalView>
      </>
    );
  }
}

export default MyEgiftCard;
