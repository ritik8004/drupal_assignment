import React from 'react';
import { fetchAPIData } from '../../../../utilities/api/apiData';
import ConditionalView from '../../../../common/components/conditional-view';
import getStringMessage from '../../../../../../../js/utilities/strings';
import { getSessionCookie, setSessionCookie, getCurrentUserEmail } from '../../../../utilities/user_util';
import { getbazaarVoiceSettings } from '../../../../utilities/api/request';

export default class AuthConfirmationMessage extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
    };
  }

  componentDidMount() {
    const bazaarVoiceSettings = getbazaarVoiceSettings();
    // Store user information in bv cookies.
    if (getSessionCookie('BvUserId') !== null && getSessionCookie('BvUserEmail') === null && getCurrentUserEmail() === null) {
      const params = `&productid=${bazaarVoiceSettings.productid}&User=${getSessionCookie('BvUserId')}&Action=`;
      const apiData = fetchAPIData('/data/submitreview.json', params);
      if (apiData instanceof Promise) {
        apiData.then((result) => {
          if (result.error === undefined
            && result.data !== undefined
            && result.data.error === undefined) {
            if (result.data.Data.Fields !== undefined
              && result.data.Data.Fields.usernickname.Value !== null
              && result.data.Data.Fields.useremail.Value !== null) {
              setSessionCookie('BvUserNickname', result.data.Data.Fields.usernickname.Value);
              setSessionCookie('BvUserEmail', result.data.Data.Fields.useremail.Value);
            }
          } else {
            Drupal.logJavascriptError('review-summary', result.error);
          }
        });
      }
    }
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
