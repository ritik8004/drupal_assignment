import React from 'react';

export default class CardNotLinkedMdcData extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      card_number: '',
    };
  }

  componentDidMount() {
    // @TODO: API call to get card number with logged in user's email id.
  }

  render() {
    const { card_number } = this.state;

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
              { card_number }
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