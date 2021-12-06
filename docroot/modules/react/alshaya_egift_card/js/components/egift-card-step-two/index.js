import React from 'react';

export default class EgiftCardStepTwo extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      active: false,
    };
  }

  render() {
    const { activate } = this.props;
    let classList = 'step-two-wrapper';

    if (activate) {
      classList = `${classList} active`;
    }

    return (
      <div className={classList}>
        <p className="step-title" style={{ width: '100%' }}>
          { Drupal.t('2. Enter Gift card details', {}, { context: 'egift' }) }
        </p>
        <div className="step-two-fields">
          <div className="egift-for-field">
            <label>
              {Drupal.t('Buying this gift for', {}, { context: 'egift' })}
              <input type="radio" name="egift-for" value={Drupal.t('Friends and family', {}, { context: 'egift' })} />
              {Drupal.t('Friends and family', {}, { context: 'egift' })}
              <input type="radio" name="egift-for" value={Drupal.t('Myself', {}, { context: 'egift' })} />
              {Drupal.t('Myself', {}, { context: 'egift' })}
            </label>
          </div>
          <div className="recipient">
            <label>
              {Drupal.t('Recipient Details', {}, { context: 'egift' })}
              <input type="text" name="egift-recipient-name" placeholder={Drupal.t('Name*', {}, { context: 'egift' })} />
              <input type="text" name="egift-recipient-email" placeholder={Drupal.t('Email*', {}, { context: 'egift' })} />
            </label>
          </div>
          <div className="egift-message">
            <label>
              {Drupal.t('Write a message', {}, { context: 'egift' })}
              <textarea name="egift-message" placeholder={Drupal.t('Message', {}, { context: 'egift' })} rows="1" />
            </label>
          </div>
        </div>
      </div>
    );
  }
}
