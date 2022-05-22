import React from 'react';

class MyPointsHistory extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isMembershipStatus: true,
    };
  }

  render() {
    const {
      isMembershipStatus,
    } = this.state;

    if (!isMembershipStatus) {
      return {};
    }

    return (
      <div className="my-points-history-wrapper">
        {/* Display points history */}
      </div>
    );
  }
}

export default MyPointsHistory;
