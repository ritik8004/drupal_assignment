import React from 'react';

import SectionTitle from '../../../utilities/section-title';

export default class FixedFields extends React.Component {

  render() {
    let country_code = window.drupalSettings.country_code;
    country_code = '975';
    return(
      <div>
          <SectionTitle>{Drupal.t('contact information')}</SectionTitle>
          <label>
           {Drupal.t('First Name')}
          <input type='text' name='fname'/>
          <div id='fname-error'></div>
          </label>
          <label>
           {Drupal.t('Last Name')}
          <input type='text' name='lname'/>
          <div id='lname-error'></div>
          </label>
          <label>
           {Drupal.t('Email')}
          <input type='email' name='email'/>
          <div id='email-error'></div>
          </label>
          <label>
           {Drupal.t('Mobile number')}
           <div>{'+' + country_code}</div>
          <input type='text' name='mobile'/>
          <div id='mobile-error'></div>
          </label>
      </div> 
    );
  }

}
