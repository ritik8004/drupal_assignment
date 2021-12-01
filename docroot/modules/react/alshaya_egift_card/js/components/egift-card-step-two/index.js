import React from 'react';

export default class EgiftCardStepTwo extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      active: false,
    };
  }

  render() {
    const { active } = this.state;
    let classList = 'step-two-wrapper';
    if (active) {
      classList = `${classList} active`;
    }

    return (
      <div className={classList}>
        <p className="step-title" style={{ width: '100%' }}>
          { Drupal.t('2. Enter Gift card details') }
        </p>
        <div className="step-two-fields">
          <div className="egift-for-field">
            <label>
              {Drupal.t('Buying this gift for')}
              <input type="radio" name="egift-for" value={Drupal.t('Friends and family')} />
              {Drupal.t('Friends and family')}
              <input type="radio" name="egift-for" value={Drupal.t('Myself')} />
              {Drupal.t('Myself')}
            </label>
          </div>
          <div className="recipient">
            <label>
              {Drupal.t('Recipient Details')}
              <input type="text" name="egift-recipient-name" placeholder={Drupal.t('Name*')} />
              <input type="text" name="egift-recipient-email" placeholder={Drupal.t('Email*')} />
            </label>
          </div>
          <div className="egift-message">
            <label>
              {Drupal.t('Write a message')}
              <textarea name="egift-message" placeholder={Drupal.t('Message')} rows="1" />
            </label>
          </div>
        </div>
      </div>
    );
  }
}
