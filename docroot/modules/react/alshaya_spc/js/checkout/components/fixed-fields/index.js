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
      <div className='spc-checkout-information'>
        <SectionTitle>{Drupal.t('contact information')}</SectionTitle>
        <div className='form-item form-type-textfield'>
          <label>{Drupal.t('First Name')}</label>
          <input type='text' name='fname' defaultValue={default_val !== '' ? default_val['firstname'] : ''}/>
          <div id='fname-error' className='error'/>
        </div>
        <div className='form-item form-type-textfield'>
          <label>{Drupal.t('Last Name')}</label>
          <input type='text' name='lname' defaultValue={default_val !== '' ? default_val['lastname'] : ''}/>
          <div id='lname-error' className='error'/>
        </div>
        <div className='form-item form-type-textfield'>
          <label>{Drupal.t('Email')}</label>
          <input type='email' name='email' defaultValue={default_val !== '' ? default_val['email'] : ''}/>
          <div id='email-error' className='error'/>
        </div>
        <div className='form-item form-type-textfield'>
          <label>{Drupal.t('Mobile number')}</label>
          <span className='country-code'>{'+' + country_mobile_code}</span>
          <input type='text' name='mobile' defaultValue={default_val !== '' ? default_val['telephone'] : ''}/>
          <div id='mobile-error' className='error'/>
        </div>
      </div>
    );
  }

}
