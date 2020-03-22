import React from 'react';

import {
  geocodeAddressToLatLng,
} from '../map/map_utils';

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

  handleChange = () => {
    const { isAddressField } = this.props;
    // Only for the address fields.
    if (isAddressField !== undefined) {
      geocodeAddressToLatLng();
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
            defaultValue={defaultValue}
            onBlur={(e) => this.handleEvent(e, 'blur')}
            className={focusClass}
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
          <span className="country-code">{`+${countryMobileCode}`}</span>
          <input
            maxLength={countryMobileCodeMaxLength}
            type="text"
            name={name}
            defaultValue={defaultValue}
          />
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
          defaultValue={defaultValue}
          onChange={this.handleChange}
          onBlur={(e) => this.handleEvent(e, 'blur')}
          className={focusClass}
        />
        <div className="c-input__bar" />
        <label>{label}</label>
        <div id={`${name}-error`} className="error" />
      </div>
    );
  }
}

export default TextField;
