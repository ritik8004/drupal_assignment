import React from 'react';
import EgiftCardLinked from './my-account-egift-card-linked';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import EgiftCardNotLinked from './my-account-egift-card-not-linked';

class MyAccount extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      linkedCard: true,
    };
  }

  render() {
    const {
      linkedCard,
    } = this.state;
    return (
      <>
        <ConditionalView condition={linkedCard === true}>
          <div className="egift-my-account">
            <EgiftCardLinked />
          </div>
        </ConditionalView>
        <ConditionalView condition={linkedCard === false}>
          <div className="egift-my-account">
            <EgiftCardNotLinked />
          </div>
        </ConditionalView>
      </>
    );
  }
}

export default MyAccount;
