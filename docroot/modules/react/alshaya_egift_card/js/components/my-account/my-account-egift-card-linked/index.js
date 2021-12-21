import React from 'react';
import moment from 'moment/moment';
import PriceElement
  from '../../../../../js/utilities/components/price/price-element';
import { callMagentoApi } from '../../../../../js/utilities/requestHelper';
import logger from '../../../../../js/utilities/logger';

class EgiftCardLinked extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      apiError: '',
    };
  }

  removeCardAction = () => {
    const { removeCard } = this.props;
    // Call magento API to remove linked eGift card.
    return callMagentoApi('/V1/egiftcard/unlinkcard', 'POST', {})
      .then((response) => {
        // Check for error from handleResponse.
        if (typeof response.data !== 'undefined' && typeof response.data.error !== 'undefined' && response.data.error) {
          this.setState({
            apiError: response.data.error_message,
          }, () => logger.error('Error while unlinking card. @error', { '@error': JSON.stringify(response.data) }));
        }

        // Remove card if no error response returned.
        if (typeof response.data !== 'undefined' && response.data.response_type === true) {
          removeCard();
        }
      });
  }

  render() {
    const { linkedCard } = this.props;
    const { apiError } = this.state;
    // Return if User linked card data is null.
    if (linkedCard === null) {
      return null;
    }

    return (
      <div className="egift-card-linked-wrapper">
        <div className="error">{ apiError }</div>
        <div className="egift-card-linked-wrapper-top">
          <div className="egift-linked-thumbnail">
            <img
              src={linkedCard.card_image}
              className="linked-card-thumbnail"
              alt={linkedCard.card_type}
              title={linkedCard.card_type}
            />
          </div>
          <div className="egift-linked-title">{Drupal.t('My eGift Card', {}, { context: 'egift' })}</div>
          <div className="egift-linked-balance">
            {Drupal.t('Balanace:')}
            <PriceElement amount={parseFloat(linkedCard.current_balance)} />
          </div>
          <button
            id="egift-remove-button"
            type="button"
            className="egift-card-remove"
            onClick={() => this.removeCardAction()}
          >
            <span className="egift-linked-card-remove">&nbsp;</span>
          </button>
        </div>
        <div className="egift-card-linked-wrapper-bottom">
          <div className="egift-linked-card-number">{Drupal.t('Gift Card number', {}, { context: 'egift' })}</div>
          <div className="egift-linked-card-number-value">{linkedCard.card_number}</div>
          <div className="egift-linked-expires">{Drupal.t('Expires on', {}, { context: 'egift' })}</div>
          <div className="egift-linked-expires-value">{moment.unix(linkedCard.expiry_date_timestamp).format('Do, MMM YYYY')}</div>
          <div className="egift-linked-card-type">{Drupal.t('Card Type', {}, { context: 'egift' })}</div>
          <div className="egift-linked-card-type-value">{linkedCard.card_type}</div>
          <button id="egift-topup-button" type="button" className="egift-topup">{Drupal.t('Top up', {}, { context: 'egift' })}</button>
        </div>
      </div>
    );
  }
}

export default EgiftCardLinked;
