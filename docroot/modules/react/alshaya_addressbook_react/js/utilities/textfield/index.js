import React from 'react';

class TextField extends React.Component {
  handleEvent = (e, handler) => {
    if (handler === 'blur') {
      if (e.currentTarget.value.length > 0) {
        e.currentTarget.classList.add('focus');
      } else {
        e.currentTarget.classList.remove('focus');
      }
    }
  };

  handleKeyDown = (e) => {
    // Remove the error text.
    const errorMsg = document.getElementById(`${e.currentTarget.name}-error`).innerHTML;
    if (errorMsg.length > 0) {
      document.getElementById(`${e.currentTarget.name}-error`).innerHTML = '';
    }
  }

  render() {
    const countryMobileCode = window.drupalSettings.country_mobile_code;
    const countryMobileCodeMaxLength = window.drupalSettings.mobile_maxlength;
    const {
      defaultValue,
      type,
      name,
      label,
      disabled,
      maxLength,
      placeholder,
      required,
    } = this.props;
    let focusClass = '';
    if (defaultValue !== undefined && defaultValue !== '') {
      focusClass = 'focus form-text';
    }

    if (type === 'email') {
      return (
        <div className="spc-type-textfield">
          <input
            type="email"
            name={name}
            disabled={disabled}
            defaultValue={defaultValue}
            onBlur={(e) => this.handleEvent(e, 'blur')}
            className={focusClass}
            placeholder={placeholder}
            message={label}
          />
          <div className="c-input__bar" />
          <label htmlFor={name}>{label}</label>
          <div id={`${name}-error`} className="error" />
        </div>
      );
    }
    if (type === 'tel') {
      const inputClass = 'spc-type-tel__input';

      return (
        <div className="form-item mobile-number-field">
          <label>{label}</label>
          <div className="mobile-input--wrapper">
            <div className="form-item form-type-select form-item-field-address-0-address-mobile-number-country-code">
              <div className="country-select">
                <div className="prefix">{`+${countryMobileCode}`}</div>
              </div>
            </div>
            <div className="form-item form-type-tel form-item-field-address-0-address-mobile-number-mobile">
              <input
                maxLength={countryMobileCodeMaxLength}
                type="tel"
                disabled={disabled}
                name={name}
                defaultValue={defaultValue}
                className={inputClass}
                message={label}
                required={required}
                tabIndex="0"
                onKeyDown={this.handleKeyDown}
              />
            </div>
          </div>
          <div className="c-input__bar" />
          <div id={`${name}-error`} className="error" />
        </div>
      );
    }

    return (
      <div className="form-item form-type-textfield">
        <input
          type="text"
          id={name}
          name={name}
          disabled={disabled}
          defaultValue={defaultValue}
          onBlur={(e) => this.handleEvent(e, 'blur')}
          className={focusClass}
          maxLength={maxLength}
          placeholder={placeholder}
          message={label}
          tabIndex="0"
          onKeyDown={this.handleKeyDown}
        />
        <div className="c-input__bar" />
        <label htmlFor={name}>{label}</label>
        <div id={`${name}-error`} className="error" />
      </div>
    );
  }
}

export default TextField;
