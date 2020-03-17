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

  handleChange = (e) => {
    // Only for the address fields.
    if (this.props.isAddressField !== undefined) {
      geocodeAddressToLatLng();
    }
  }

  render() {
    const countryMobileCode = window.drupalSettings.country_mobile_code;
    const countryMobileCodeMaxLength = window.drupalSettings.mobile_maxlength;
    let focusClass = '';
    if (this.props.defaultValue !== undefined || this.props.defaultValue !== '') {
      if (this.props.defaultValue.length > 0) {
        focusClass = 'focus';
      }
    }

    if (this.props.type === 'email') {
      return (
        <div className="spc-type-textfield">
          <input
            type="email"
            name={this.props.name}
            defaultValue={this.props.defaultValue}
            onBlur={(e) => this.handleEvent(e, 'blur')}
            className={focusClass}
          />
          <div className="c-input__bar" />
          <label>{this.props.label}</label>
          <div id={`${this.props.name}-error`} className="error" />
        </div>
      );
    }
    if (this.props.type === 'tel') {
      return (
        <div className="spc-type-tel">
          <label>{this.props.label}</label>
          <span className="country-code">{`+${countryMobileCode}`}</span>
          <input
            maxLength={countryMobileCodeMaxLength}
            type="text"
            name={this.props.name}
            defaultValue={this.props.defaultValue}
          />
          <div className="c-input__bar" />
          <div id={`${this.props.name}-error`} className="error" />
        </div>
      );
    }

    return (
      <div className="spc-type-textfield">
        <input
          type="text"
          id={this.props.name}
          name={this.props.name}
          defaultValue={this.props.defaultValue}
          onChange={this.handleChange}
          onBlur={(e) => this.handleEvent(e, 'blur')}
          className={focusClass}
        />
        <div className="c-input__bar" />
        <label>{this.props.label}</label>
        <div id={`${this.props.name}-error`} className="error" />
      </div>
    );
  }
}

export default TextField;
