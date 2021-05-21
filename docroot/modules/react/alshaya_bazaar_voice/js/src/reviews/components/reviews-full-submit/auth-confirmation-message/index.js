import React from 'react';
import ConditionalView from '../../../../common/components/conditional-view';
import getStringMessage from '../../../../../../../js/utilities/strings';

export default class AuthConfirmationMessage extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
    };
  }

  render() {
    const {
      isUserVerified,
    } = this.props;

    return (
      <ConditionalView condition={isUserVerified === true}>
        <div className="auth-confirmation-message">
          <h1>{getStringMessage('bv_auth_confirmation_message')}</h1>
          <div className="submission-msg">{getStringMessage('submission_msg')}</div>
        </div>
      </ConditionalView>
    );
  }
}
