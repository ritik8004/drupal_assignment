import React from 'react';

import SectionTitle from '../../../utilities/section-title';

export default class FixedFields extends React.Component {

  render() {
    let country_mobile_code = window.drupalSettings.country_mobile_code;
    let default_val = '';
    if (this.props.default_val.length !== 0 && this.props.default_val.length !== 'undefined') {
      default_val = this.props.default_val;
    }

    return(
      <div className='spc-checkout-contact-information'>
        <SectionTitle>{Drupal.t('contact information')}</SectionTitle>
        <div className='spc-checkout-contact-information-fields'>
          <div className='spc-type-textfield'>
            <input type='text' name='fname' required='required' defaultValue={default_val !== '' ? default_val['firstname'] : ''}/>
            <div className='c-input__bar'/>
            <label>{Drupal.t('First Name')}</label>
            <div id='fname-error' className='error'/>
          </div>
          <div className='spc-type-textfield'>
            <input type='text' name='lname' required='required' defaultValue={default_val !== '' ? default_val['lastname'] : ''}/>
            <div className='c-input__bar'/>
            <label>{Drupal.t('Last Name')}</label>
            <div id='lname-error' className='error'/>
          </div>
          <div className='spc-type-textfield'>
            <input type='email' name='email' required='required' defaultValue={default_val !== '' ? default_val['email'] : ''}/>
            <div className='c-input__bar'/>
            <label>{Drupal.t('Email')}</label>
            <div id='email-error' className='error'/>
          </div>
          <div className='spc-type-tel'>
            <label>{Drupal.t('Mobile number')}</label>
            <span className='country-code'>{'+' + country_mobile_code}</span>
            <input type='text' name='mobile' required='required' defaultValue={default_val !== '' ? default_val['telephone'] : ''}/>
            <div className='c-input__bar'/>
            <div id='mobile-error' className='error'/>
          </div>
        </div>
      </div>
    );
  }

}
