import React from 'react';
import Popup from 'reactjs-popup';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';

class MembershipPopup extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isModelOpen: false,
    };
  }

  componentDidMount() {
    // Listen to `helloMemberPointsLoaded` event which will return if customer is new hello member.
    document.addEventListener('helloMemberPointsLoaded', this.getCustomerData, false);
  }

  getCustomerData = (e) => {
    const data = e.detail;
    // Only if user is new hello member show popup message.
    if (hasValue(data) && data.is_new_hello_member === 1) {
      this.setState({
        isModelOpen: true,
      });
    }
  };

  closeModal = (e) => {
    e.preventDefault();
    document.body.classList.remove('open-form-modal');

    this.setState({
      isModelOpen: false,
    });
  };

  render() {
    const { isModelOpen } = this.state;

    return (
      <>
        <Popup
          open={isModelOpen}
          className="hello_member_popup"
          closeOnDocumentClick={false}
          closeOnEscape={false}
        >
          <div className="hello-member-popup-form">
            <div className="hello-membership-info">
              <div className="hello-membership-title">
                {Drupal.t('Hello Member', {}, { context: 'hello_member' })}
              </div>
              <div className="hello-membership-details">
                <p className="hello-membership-sub-title">
                  {Drupal.t('Your experience just got better! With the updated ', {}, { context: 'hello_member' })}
                  <a href="@hm-terms-url">{Drupal.t('Terms & Conditions', {}, { context: 'hello_member' })}</a>
                  {Drupal.t(', you\'re now a Member and can enjoy a wide range of benefits and new features. Come and get free delivery and 20% off your next purchase!', {}, { context: 'hello_member' })}
                </p>
                <div className="hello-membership-continue">
                  <a onClick={(e) => this.closeModal(e)}>Continue</a>
                </div>
                <p className="hello-membership-terms">
                  <a href="@hm-terms-url">{Drupal.t('Click here', {}, { context: 'hello_member' })}</a>
                  {Drupal.t(' to read more about Hello Member programme.', {}, { context: 'hello_member' })}
                </p>
                <p className="hello-membership-terms">
                  {Drupal.t('Read the updated ', {}, { context: 'hello_member' })}
                  <a href="@hm-terms-url">{Drupal.t('Terms & Conditions', {}, { context: 'hello_member' })}</a>
                  {Drupal.t(' and ', {}, { context: 'hello_member' })}
                  <a href="@hm-privacy-policy">{Drupal.t('Privacy Policy.', {}, { context: 'hello_member' })}</a>
                </p>
                <p className="hello-membership-terms">
                  {Drupal.t('If you don\'t want to be part of the programme, ', {}, { context: 'hello_member' })}
                  <a href="@hm-contact-us">{Drupal.t('contact us.', {}, { context: 'hello_member' })}</a>
                </p>
              </div>
            </div>
          </div>
        </Popup>
      </>
    );
  }
}

export default MembershipPopup;
