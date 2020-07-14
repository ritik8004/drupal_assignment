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

  handleChange = (e) => {
    const { handleChange } = this.props;
    handleChange(e);
  }

  render() {
    const countryMobileCode = drupalSettings.alshaya_appointment.country_mobile_code;
    const countryMobileCodeMaxLength = drupalSettings.alshaya_appointment.mobile_maxlength;
    const {
      defaultValue,
      type,
      name,
      label,
      required,
    } = this.props;
    let focusClass = '';
    if (defaultValue !== undefined && defaultValue !== '') {
      focusClass = 'focus';
    }

    if (type === 'email') {
      return (
        <div className="appointment-type-textfield">
          <input
            type="email"
            id={name}
            name={name}
            defaultValue={defaultValue}
            onBlur={(e) => this.handleEvent(e, 'blur')}
            className={focusClass}
            onChange={this.handleChange}
            required={required}
          />
          <div className="c-input__bar" />
          <label>{label}</label>
          <div id={`${name}-error`} className="error" />
        </div>
      );
    }
    if (type === 'mobile') {
      return (
        <div className="appointment-type-mobile">
          <label>{label}</label>
          <div className="field-wrapper">
            <span className="country-code">{`+${countryMobileCode}`}</span>
            <input
              maxLength={countryMobileCodeMaxLength}
              type="text"
              id={name}
              name={name}
              defaultValue={defaultValue}
              onChange={this.handleChange}
              required={required}
            />
          </div>
          <div className="c-input__bar" />
          <div id={`${name}-error`} className="error" />
        </div>
      );
    }
    if (type === 'date') {
      return (
        <div className="appointment-type-date">
          <input
            type="date"
            id={name}
            name={name}
            defaultValue={defaultValue}
            onChange={this.handleChange}
            onBlur={(e) => this.handleEvent(e, 'blur')}
            className={focusClass}
            required={required}
          />
          <div className="c-input__bar" />
          <label>{label}</label>
          <div id={`${name}-error`} className="error" />
        </div>
      );
    }

    return (
      <div className="appointment-type-textfield">
        <input
          type="text"
          id={name}
          name={name}
          defaultValue={defaultValue}
          onChange={this.handleChange}
          onBlur={(e) => this.handleEvent(e, 'blur')}
          className={focusClass}
          required={required}
        />
        <div className="c-input__bar" />
        <label>{label}</label>
        <div id={`${name}-error`} className="error" />
      </div>
    );
  }
}

export default TextField;
