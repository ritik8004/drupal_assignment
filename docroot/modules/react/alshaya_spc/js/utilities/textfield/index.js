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
    } = this.props;
    let focusClass = '';
    if (defaultValue !== undefined && defaultValue !== '') {
      focusClass = 'focus';
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
          />
          <div className="c-input__bar" />
          <label>{label}</label>
          <div id={`${name}-error`} className="error" />
        </div>
      );
    }
    if (type === 'tel') {
      return (
        <div className="spc-type-tel">
          <label>{label}</label>
          <div className="field-wrapper">
            <span className="country-code">{`+${countryMobileCode}`}</span>
            <input
              maxLength={countryMobileCodeMaxLength}
              disabled={disabled}
              type="tel"
              name={name}
              defaultValue={defaultValue}
            />
          </div>
          <div className="c-input__bar" />
          <div id={`${name}-error`} className="error" />
        </div>
      );
    }

    return (
      <div className="spc-type-textfield">
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
        />
        <div className="c-input__bar" />
        <label>{label}</label>
        <div id={`${name}-error`} className="error" />
      </div>
    );
  }
}

export default TextField;
