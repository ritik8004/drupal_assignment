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

    let hasSubTitle = this.props.subTitle !== undefined && this.props.subTitle.length > 0
      ? 'subtitle-yes' : 'subtitle-no';

    return (
      <div className={'spc-checkout-contact-information ' + hasSubTitle} id='spc-checkout-contact-info'>
        <div className='spc-contact-information-header'>
          <SectionTitle>{Drupal.t('contact information')}</SectionTitle>
          <span className='spc-contact-info-desc'>{this.props.subTitle}</span>
        </div>
        <div className='spc-checkout-contact-information-fields'>
          <TextField type='text' required={false} name='fullname' defaultValue={default_val !== '' ? default_val['fullname'] : ''} label={Drupal.t('Full Name')}/>
          {this.props.showEmail &&
            <TextField type='email' name='email' defaultValue={default_val !== '' ? default_val['email'] : ''} label={Drupal.t('Email')}/>
          }
          <TextField type='tel' name='mobile' defaultValue={default_val !== '' ? default_val['telephone'] : ''} label={Drupal.t('Mobile Number')}/>
          < input type = 'hidden' name = 'address_id' value = {default_val !== '' && default_val['address_id'] !== null ? default_val['address_id'] : 0}/>
        </div>
      </div>
    );
  }

}
