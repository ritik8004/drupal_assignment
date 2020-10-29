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
        <div className="linking-option" onClick={(e) => this.selectOption(e)}>
          <input
            type="radio"
            id="email"
            name="linking-options"
            value="email"
            className="linking-option-radio"
            checked={selectedOption === 'email'}
          />
          <label
            className="aura-radio-sim"
            htmlFor="email"
          >
            {Drupal.t('Email address')}
          </label>
        </div>
        <div className="linking-option" onClick={(e) => this.selectOption(e)}>
          <input
            type="radio"
            id="card"
            name="linking-options"
            value="card"
            className="linking-option-radio"
            checked={selectedOption === 'card'}
          />
          <label
            className="aura-radio-sim"
            htmlFor="card"
          >
            {Drupal.t('Card number')}
          </label>
        </div>
        <div className="linking-option" onClick={(e) => this.selectOption(e)}>
          <input
            type="radio"
            id="mobile"
            name="linking-options"
            value="mobile"
            className="linking-option-radio"
            checked={selectedOption === 'mobile'}
          />
          <label
            className="aura-radio-sim"
            htmlFor="mobile"
          >
            {Drupal.t('Mobile number')}
          </label>
        </div>
      </div>
    );
  }
}

export default AuraFormLinkCardOptions;
