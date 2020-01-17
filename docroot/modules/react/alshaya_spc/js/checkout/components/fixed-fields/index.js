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
      <div>
          <SectionTitle>{Drupal.t('contact information')}</SectionTitle>
          <label>
           {Drupal.t('First Name')}
          <input type='text' name='fname' defaultValue={default_val !== '' ? default_val['firstname'] : ''}/>
          <div id='fname-error'></div>
          </label>
          <label>
           {Drupal.t('Last Name')}
          <input type='text' name='lname' defaultValue={default_val !== '' ? default_val['lastname'] : ''}/>
          <div id='lname-error'></div>
          </label>
          <label>
           {Drupal.t('Email')}
          <input type='email' name='email' defaultValue={default_val !== '' ? default_val['email'] : ''}/>
          <div id='email-error'></div>
          </label>
          <label>
           {Drupal.t('Mobile number')}
           <div>{'+' + country_mobile_code}</div>
          <input type='text' name='mobile' defaultValue={default_val !== '' ? default_val['telephone'] : ''}/>
          <div id='mobile-error'></div>
          </label>
      </div> 
    );
  }

}
