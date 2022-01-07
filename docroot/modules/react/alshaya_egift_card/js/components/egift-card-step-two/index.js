import React from 'react';
import TextareaAutosize from 'react-autosize-textarea';
import ConditionalView
  from '../../../../js/utilities/components/conditional-view';

export default class EgiftCardStepTwo extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      showMessageField: true, // Show or hide message field.
      egiftMessage: '',
      textAreaCount: 0,
    };
  }

  handleEvent = (e) => {
    if (e.currentTarget.value.length > 0) {
      e.currentTarget.classList.add('focus');
    } else {
      e.currentTarget.classList.remove('focus');
    }
    this.setState({ egiftMessage: encodeURIComponent(e.target.value) });
    this.setState({ textAreaCount: e.target.value.length });
  };

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
    const { showMessageField, egiftMessage, textAreaCount } = this.state;
    const { activate } = this.props;
    let classList = 'step-wrapper step-two-wrapper';

    if (activate) {
      classList = `${classList} active`;
    }

    return (
      <div className={classList}>
        <p className="step-title fadeInUp">
          { Drupal.t('2. Enter Gift card details', {}, { context: 'egift' }) }
        </p>
        <ConditionalView condition={activate === true}>
          <div className="step-two-fields fadeInUp">
            <div
              className="egift-for-field egift-input-field-container"
            >
              <div className="egift-input-title">
                {Drupal.t('Buying this gift for', {}, { context: 'egift' })}
              </div>
              <div className="egift-input-field-wrapper">
                <div className="egift-input-field-item">
                  <input
                    defaultChecked={showMessageField}
                    type="radio"
                    name="egift-for"
                    id="egiftFor-friends-family"
                    value="Friends and family"
                    onChange={(e) => this.handleChange(e)}
                  />
                  <label htmlFor="egiftFor-friends-family">
                    {Drupal.t('Friends and family', {}, { context: 'egift' })}
                  </label>
                </div>
                <div className="egift-input-field-item">
                  <input
                    type="radio"
                    name="egift-for"
                    id="egiftFor-myself"
                    value="Myself"
                    onChange={(e) => this.handleChange(e)}
                  />
                  <label htmlFor="egiftFor-myself">
                    {Drupal.t('Myself', {}, { context: 'egift' })}
                  </label>
                </div>
              </div>
            </div>
            <div className="recipient egift-input-field-container">
              <div id="recipient-label" className="egift-input-title">
                {Drupal.t('Recipient Details', {}, { context: 'egift' })}
              </div>
              <div className="egift-input-textfield-wrapper">
                <div className="egift-input-textfield-item egift-input-textfield-name">
                  <input
                    type="text"
                    name="egift-recipient-name"
                    onBlur={(e) => this.handleEvent(e)}
                  />
                  <div className="c-input__bar" />
                  <label>{Drupal.t('Name*', {}, { context: 'egift' })}</label>
                  <div id="fullname-error" className="error egift-error" />
                </div>
                <div className="egift-input-textfield-item egift-input-textfield-email">
                  <input
                    type="email"
                    name="egift-recipient-email"
                    onBlur={(e) => this.handleEvent(e)}
                  />
                  <div className="c-input__bar" />
                  <label>{Drupal.t('Email*', {}, { context: 'egift' })}</label>
                  <div id="email-error" className="error egift-error" />
                </div>
              </div>
            </div>
            <ConditionalView condition={showMessageField === true}>
              <div className="egift-message egift-input-field-container">
                <div className="egift-input-title">
                  {Drupal.t('Write a message', {}, { context: 'egift' })}
                </div>
                <div className="egift-input-textfield-wrapper">
                  <div className="egift-input-textfield-item">
                    <TextareaAutosize
                      type="text"
                      id="egift-message"
                      name="egift-message"
                      maxLength={parseInt(200, 10)}
                      onChange={this.handleEvent}
                      onBlur={(e) => this.handleEvent(e)}
                      className="form-input"
                      defaultValue={egiftMessage}
                    />
                    <div className="c-input__bar" />
                    <label>{Drupal.t('Message', {}, { context: 'egift' })}</label>
                    <div className="textarea-character-limit">{ parseInt(200, 10) - parseInt(textAreaCount, 10) }</div>
                    <div id="email-error" className="error egift-error" />
                  </div>
                </div>
              </div>
            </ConditionalView>
          </div>
        </ConditionalView>
      </div>
    );
  }
}
