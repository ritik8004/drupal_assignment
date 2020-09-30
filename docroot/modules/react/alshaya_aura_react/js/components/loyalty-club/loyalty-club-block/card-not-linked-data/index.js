import React from 'react';
import { getAPIData } from '../../../../utilities/api/fetchApiData';

export default class CardNotLinkedData extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      cardNumber: '',
    };
  }

  componentDidMount() {
    // API call to get card number with logged in user's email id.
    const apiUrl = 'get/loyalty-club/get-apc-user-details-by-email';
    const apiData = getAPIData(apiUrl);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.data !== undefined && result.data.error === undefined) {
          this.setState({
            cardNumber: result.data.apcCard,
          });
        }
      });
    }
  }

  render() {
    const { cardNumber } = this.state;

    return (
      <div className="aura-card-not-linked-mdc-data-wrapper">
        <div className="aura-logo">
          AURA logo placeholder
        </div>
        <div className="aura-card-not-linked-mdc-data-description">
          <div className="header">
            { Drupal.t('We see a loyalty card associate with your email. It just a takes one click to link. Do you want to link now?') }
          </div>
          <div className="card-number">
            <span>
              { cardNumber }
            </span>
            <a href="">
              { Drupal.t('Not You?')}
            </a>
          </div>
          <div className="link-your-card">
            { Drupal.t('Already in Loyalty Club') }
            <a href="">
              { Drupal.t('LINK YOUR CARD NOW') }
            </a>
          </div>
        </div>
      </div>
    );
  }
}
