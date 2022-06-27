import React from 'react';
import Popup from 'reactjs-popup';
import MembershipInfo from '../membership-info';
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
            <a className="close-modal" onClick={(e) => this.closeModal(e)} />
            <MembershipInfo />
          </div>
        </Popup>
      </>
    );
  }
}

export default MembershipPopup;
