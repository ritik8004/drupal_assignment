import React from 'react';
import TextareaAutosize from 'react-autosize-textarea';
import ConditionalView
  from '../../../../js/utilities/components/conditional-view';
import { getTextAreaMaxLength } from '../../utilities';

export default class EgiftCardStepTwo extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      showMessageField: true, // Show or hide message field.
      egiftMessage: '',
      textAreaCount: 0,
    };
  }

  /**
   * Handles onBlur to add remove focus class on fields.
   */
  handleEvent = (e) => {
    if (e.currentTarget.value.length > 0) {
      e.currentTarget.classList.add('focus');
    } else {
      e.currentTarget.classList.remove('focus');
    }
  };

  /**
   * Handles textarea field count.
   */
  handleTextAreaChange = (e) => {
    // Set the value of the text area when cursor moves out so label stays on top.
    document.getElementById('egift-message').value = e.target.value;

    // Get the count of the characters in textarea.
    const count = parseInt(getTextAreaMaxLength(), 10) - parseInt(e.target.value.length, 10);

    // Show the count of characters when user types in textarea.
    document.getElementById('textarea-count').innerHTML = count.toString();
  }

  /**
   * Egift show or hide message field on selecting for field.
   */
  handleChange = (e) => {
    const eGiftFor = e.target.value;
    // Hide message field if egift card is for self
    // and show if its for friends and family.
    const eGiftForLabelElement = document.getElementById('recipient-label');
    if (eGiftFor === 'self') {
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

  /**
   * Show translated email HTML5 validation error.
   */
  emailValidate = (e) => {
    // Get target element.
    const ele = e.target;
    if (typeof ele !== 'undefined' && ele.validity.typeMismatch) {
      ele.setCustomValidity(Drupal.t('Please enter valid email address', {}, { context: 'egift' }));
    } else {
      ele.setCustomValidity('');
    }

    return true;
  }

  /**
   * Block user from entering emojis in email field.
   */
  removeEmojisFromEmail = (e) => {
    const element = e.target;
    // Replace emojis and special character except @, +, ., _ is allowed.
    element.value = element.value.replace(/[^\p{L}\p{N}\p{Z}{+_.@\n}]/gu, '');
  }

  /**
   * Block user from entering emojis in name field.
   */
  removeEmojisFromName = (e) => {
    const element = e.target;
    // Replace emojis and special characters.
    element.value = element.value.replace(/[^\p{L}\p{Z}{\n}]/gu, '');
  }

  /**
   * Block user from entering emojis in text field.
   */
  removeEmojisFromText = (e) => {
    const element = e.target;
    // Replace emojis and special characters except punctuations.
    element.value = element.value.replace(/[^\p{L}\p{N}\p{P}\p{Z}\n]/gu, '');
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
                    value="friends"
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
                    value="self"
                    onChange={(e) => this.handleChange(e)}
                    defaultChecked={!showMessageField}
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
                    onInput={(e) => this.removeEmojisFromName(e)}
                  />
                  <div className="c-input__bar" />
                  <label>{Drupal.t('Name*', {}, { context: 'egift' })}</label>
                  <div id="fullname-error" className="error egift-error" />
                </div>
                <div className="egift-input-textfield-item egift-input-textfield-email">
                  <input
                    type="email"
                    name="egift-recipient-email"
                    onChange={(e) => this.emailValidate(e)}
                    onBlur={(e) => this.handleEvent(e)}
                    onInvalid={(e) => {
                      e.target.setCustomValidity(Drupal.t('Please enter valid email address', {}, { context: 'egift' }));
                    }}
                    onInput={(e) => this.removeEmojisFromEmail(e)}
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
                      maxLength={parseInt(getTextAreaMaxLength(), 10)}
                      onChange={(e) => this.handleTextAreaChange(e)}
                      onBlur={(e) => this.handleEvent(e)}
                      className="form-input"
                      defaultValue={egiftMessage}
                      onInput={(e) => this.removeEmojisFromText(e)}
                    />
                    <div className="c-input__bar" />
                    <label>{Drupal.t('Message', {}, { context: 'egift' })}</label>
                    <div
                      id="textarea-count"
                      className="textarea-character-limit"
                    >
                      { parseInt(getTextAreaMaxLength(), 10) - parseInt(textAreaCount, 10) }
                    </div>
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
