import React from 'react';
import ConditionalView
  from '../../../../js/utilities/components/conditional-view';

export default class EgiftCardStepTwo extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      showMessageField: true, // Show or hide message field.
    };
  }

  /**
   * Egift show or hide message field on selecting for field.
   */
  handleChange = (e) => {
    const eGiftFor = e.target.value;
    // Hide message field if egift card is for self
    // and show if its for friends and family.
    const eGiftForLabelElement = document.getElementById('recipient-label');
    if (eGiftFor === 'Myself') {
      // Update label
      eGiftForLabelElement.innerHTML = Drupal.t('My Details', {}, { context: 'egift' });
      this.setState({
        showMessageField: false,
      });
    } else {
      // Update label
      eGiftForLabelElement.innerHTML = Drupal.t('Recipient Details', {}, { context: 'egift' });
      this.setState({
        showMessageField: true,
      });
    }
  }

  render() {
    const { showMessageField } = this.state;
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
        <ConditionalView condition={activate === true}>
          <div className="step-two-fields">
            <div
              className="egift-for-field"
              onChange={(e) => this.handleChange(e)}
            >
              <label>
                {Drupal.t('Buying this gift for', {}, { context: 'egift' })}
                <input
                  defaultChecked={showMessageField}
                  type="radio"
                  name="egift-for"
                  value="Friends and family"
                />
                {Drupal.t('Friends and family', {}, { context: 'egift' })}
                <input
                  type="radio"
                  name="egift-for"
                  value="Myself"
                />
                {Drupal.t('Myself', {}, { context: 'egift' })}
              </label>
            </div>
            <div className="recipient">
              <label>
                <span id="recipient-label">
                  {Drupal.t('Recipient Details', {}, { context: 'egift' })}
                </span>
                <input
                  type="text"
                  name="egift-recipient-name"
                  placeholder={Drupal.t('Name*', {}, { context: 'egift' })}
                />
                <div id="fullname-error" />
                <input
                  type="email"
                  name="egift-recipient-email"
                  placeholder={Drupal.t('Email*', {}, { context: 'egift' })}
                />
                <div id="email-error" />
              </label>
            </div>
            <ConditionalView condition={showMessageField === true}>
              <div className="egift-message">
                <label>
                  {Drupal.t('Write a message', {}, { context: 'egift' })}
                  <textarea
                    name="egift-message"
                    placeholder={Drupal.t('Message', {}, { context: 'egift' })}
                    rows="1"
                  />
                </label>
              </div>
            </ConditionalView>
          </div>
        </ConditionalView>
      </div>
    );
  }
}
