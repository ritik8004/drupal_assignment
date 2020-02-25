import React from 'react';

import SectionTitle from '../../../utilities/section-title';
import TextField from '../../../utilities/textfield';

export default class FixedFields extends React.Component {

  render() {
    let default_val = '';
    if (this.props.default_val.length !== 0
      && this.props.default_val.length !== 'undefined') {
      default_val = this.props.default_val['static'];
    }

    return (
      <div className='spc-checkout-contact-information'>
        <SectionTitle>{Drupal.t('contact information')}</SectionTitle>
        <div className='spc-checkout-contact-information-fields'>
          <TextField type='text' required={true} name='fname' defaultValue={default_val !== '' ? default_val['firstname'] : ''} label={Drupal.t('First Name')}/>
          <TextField type='text' required={true} name='lname' defaultValue={default_val !== '' ? default_val['lastname'] : ''} label={Drupal.t('Last Name')}/>
          {this.props.showEmail &&
            <TextField type='email' name='email' defaultValue={default_val !== '' ? default_val['email'] : ''} label={Drupal.t('Email')}/>
          }
          <TextField type='tel' name='mobile' defaultValue={default_val !== '' ? default_val['telephone'] : ''} label={Drupal.t('Mobile Number')}/>
        </div>
      </div>
    );
  }

}
