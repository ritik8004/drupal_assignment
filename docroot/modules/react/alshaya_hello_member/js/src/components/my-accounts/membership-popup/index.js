import React from 'react';
import Popup from 'reactjs-popup';
import MembershipInfo from '../membership-info';

class MembershipPopup extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isModelOpen: false,
    };
  }

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
            <MembershipInfo />
          </div>
        </Popup>
      </>
    );
  }
}

export default MembershipPopup;
