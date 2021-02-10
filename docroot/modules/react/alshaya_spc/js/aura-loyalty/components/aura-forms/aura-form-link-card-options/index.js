import React from 'react';

class AuraFormLinkCardOptions extends React.Component {
  constructor(props) {
    super(props);
    this.optionsRef = React.createRef();
  }

  selectOption = (e) => {
    const { selectOptionCallback } = this.props;

    // Clear selection.
    document.querySelectorAll('.linking-option-radio').forEach((item) => {
      const element = item;
      element.checked = false;
    });
    e.target.firstChild.checked = true;
    selectOptionCallback(e.target.firstChild.value);
  };

  render() {
    const { selectedOption } = this.props;

    return (
      <div ref={this.optionsRef} className="aura-form-items-link-card-options">
        <div key="mobile" className="linking-option" onClick={(e) => this.selectOption(e)}>
          <input
            type="radio"
            id="mobile"
            name="linking-options"
            value="mobile"
            className="linking-option-radio"
            defaultChecked={selectedOption === 'mobile'}
          />
          <label
            className="aura-radio-sim"
            htmlFor="mobile"
          >
            {Drupal.t('Mobile Number')}
          </label>
        </div>
        <div key="card" className="linking-option" onClick={(e) => this.selectOption(e)}>
          <input
            type="radio"
            id="card"
            name="linking-options"
            value="cardNumber"
            className="linking-option-radio"
            defaultChecked={selectedOption === 'cardNumber'}
          />
          <label
            className="aura-radio-sim"
            htmlFor="card"
          >
            {Drupal.t('Card number')}
          </label>
        </div>
        <div key="email" className="linking-option" onClick={(e) => this.selectOption(e)}>
          <input
            type="radio"
            id="email"
            name="linking-options"
            value="email"
            className="linking-option-radio"
            defaultChecked={selectedOption === 'email'}
          />
          <label
            className="aura-radio-sim"
            htmlFor="email"
          >
            {Drupal.t('Email address')}
          </label>
        </div>
      </div>
    );
  }
}

export default AuraFormLinkCardOptions;
