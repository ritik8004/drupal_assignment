import React from 'react';
import Popup from 'reactjs-popup';
import parse from 'html-react-parser';
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

    const contactUsElement = document.querySelector("a[classname='contact-us']");

    document.addEventListener('mousedown', (e) => {
      if (contactUsElement.contains(e.target)) {
        this.contactUsClick();
      }
    });
  }

  getCustomerData = (e) => {
    const data = e.detail;
    // Only if user is new hello member show popup message.
    if (hasValue(data) && data.is_new_hello_member === 1) {
      this.setState({
        isModelOpen: true,
      });
      // Push hello member enroll popup display to gtm.
      Drupal.alshayaSeoGtmPushHmAutoEnrollView();
    }
  };

  closeModal = (e) => {
    e.preventDefault();
    document.body.classList.remove('open-form-modal');

    this.setState({
      isModelOpen: false,
    });
  };

  contactUsClick = () => {
    window.dataLayer.push({
      event: 'pop-up',
      eventProps: {
        category: 'pop-up',
        action: 'hmAutoEnroll-click-contact_us',
      },
    });
  };

  render() {
    const { isModelOpen } = this.state;
    const { popupTextAbove, popupTextBelow } = drupalSettings.helloMember;

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
                {hasValue(popupTextAbove) ? parse(popupTextAbove) : ''}
                <div className="hello-membership-continue">
                  <a onClick={(e) => this.closeModal(e)}>{Drupal.t('Continue', {}, { context: 'hello_member' })}</a>
                </div>
                {hasValue(popupTextBelow) ? parse(popupTextBelow) : ''}
              </div>
            </div>
          </div>
        </Popup>
      </>
    );
  }
}

export default MembershipPopup;
