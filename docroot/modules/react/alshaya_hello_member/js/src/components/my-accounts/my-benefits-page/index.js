import React from 'react';

class MyBenefitsPage extends React.Component {
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
      <div className="my-benefits-page-wrapper">
        display my benefits in page.
        {/* Display my benefits on page in details */}
      </div>
    );
  }
}

export default MyBenefitsPage;
