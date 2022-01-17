import React from 'react';
import EgiftCardLinked from './my-account-egift-card-linked';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import EgiftCardNotLinked from './my-account-egift-card-not-linked';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../../js/utilities/showRemoveFullScreenLoader';
import Loading from '../../../../js/utilities/loading';
import { callEgiftApi } from '../../../../js/utilities/egiftCardHelper';

class MyEgiftCard extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: true, // Flag to check api call is complete.
      linkedCard: null,
    };
  }

  componentDidMount() {
    // Get user linked card.
    this.getUserLinkedCard();
  }

  /**
   * Get User linked card helper.
   */
  getUserLinkedCard = () => {
    // Call to get customer linked card details.
    const result = callEgiftApi('eGiftHpsCustomerData', 'GET', {});
    if (result instanceof Promise) {
      showFullScreenLoader();
      result.then((response) => {
        removeFullScreenLoader();
        if (typeof response.data !== 'undefined' && response.data.response_type) {
          this.setState({
            linkedCard: response.data,
            wait: false,
          });
        } else {
          // Set wait to false to show link card form.
          this.setState({
            wait: false,
          });
        }
      });
    }
  };

  /**
   * Handle remove linked card.
   */
  removeCard = () => {
    this.setState({
      linkedCard: null,
    });
  }

  /**
   * Shows card after user links the card.
   */
  showCard = () => {
    this.getUserLinkedCard();
  };

  render() {
    const { wait, linkedCard } = this.state;
    // Return if API call for Users linkedCard is not complete.
    if (wait) {
      return (
        <div className="egift-my-account">
          <Loading />
        </div>
      );
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
