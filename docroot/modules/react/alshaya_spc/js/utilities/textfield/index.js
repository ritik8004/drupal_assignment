import React from 'react';

class TextField extends React.Component {
  render() {
    let countryMobileCode = window.drupalSettings.country_mobile_code;
    let countryMobileCodeMaxLength = window.drupalSettings.mobile_maxlength;

    if (this.props.type === 'email') {
      return (
        <div className='spc-type-textfield'>
          <input type='email' name={this.props.name} required='required' defaultValue={this.props.defaultValue}/>
          <div className='c-input__bar'/>
          <label>{this.props.label}</label>
          <div id={this.props.name + '-error'} className='error'/>
        </div>
      );
    }
    else if (this.props.type === 'tel') {
      return (
        <div className='spc-type-tel'>
          <label>{this.props.label}</label>
          <span className='country-code'>{'+' + countryMobileCode}</span>
          <input maxLength={countryMobileCodeMaxLength}
                 type='text' name={this.props.name} required='required'
                 defaultValue={this.props.defaultValue}/>
          <div className='c-input__bar'/>
          <div id={this.props.name + '-error'} className='error'/>
        </div>
      );
    }
    else {
      return (
        <div className='spc-type-textfield'>
          <input type='text' id={this.props.id} name={this.props.name} required={this.props.required} defaultValue={this.props.defaultValue}/>
          <div className='c-input__bar'/>
          <label>{this.props.label}</label>
          <div id={this.props.name + '-error'} className='error'/>
        </div>
      );
    }

  }
}

export default TextField;
