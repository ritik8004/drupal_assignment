import React from 'react';
import Popup from 'reactjs-popup';
import MembershipInfo from '../membership-info';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';

class MembershipPopup extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isModelOpen: true,
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
            <MembershipInfo close={this.closeModal.bind(this)}/>
          </div>
        </Popup>
      </>
    );
  }
}

export default MembershipPopup;
