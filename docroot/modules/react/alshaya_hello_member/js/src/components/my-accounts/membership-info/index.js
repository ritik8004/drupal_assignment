import React from 'react';

class MembershipInfo extends React.Component {
  render() {
    return (
      <div className="hello-membership-info">
        <div className="hello-membership-title">
          {Drupal.t('Hello Member', {}, { context: 'hello_member' })}
        </div>
        <div className="hello-membership-details">
          <p className="hello-membership-sub-title">{Drupal.t('Your experience just got better! With the updated Terms & Conditions, you\'re now a Member and can enjoy a wide range of benefits and new features. Come and get free delivery and 20% off your next purchase!', {}, { context: 'hello_member' })}</p>
          <div className='hello-membership-continue'><a onClick={(e) => this.props.close(e)}>Continue</a></div>
          <p className='hello-membership-terms'>{Drupal.t('Click here to read more about Hello Member programme.', {}, { context: 'hello_member' })}</p>
          <p className='hello-membership-terms'>{Drupal.t('Read the updated Terms & Conditions and Privacy Policy.', {}, { context: 'hello_member' })}</p>
          <p className='hello-membership-terms'>{Drupal.t('If you don\'t want to be part of the programme, contact us.', {}, { context: 'hello_member' })}</p>
        </div>
      </div>
    )
  }
}

export default MembershipInfo;
