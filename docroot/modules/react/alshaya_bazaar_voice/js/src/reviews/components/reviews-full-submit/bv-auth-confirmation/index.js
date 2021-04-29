import React from 'react';
import Popup from 'reactjs-popup';
import { postAPIData, fetchAPIData } from '../../../../utilities/api/apiData';
import BazaarVoiceMessages from '../../../../common/components/bazaarvoice-messages';
import {
  setSessionCookie, getSessionCookie, getCurrentUserEmail,
  getUserNicknameKey,
} from '../../../../utilities/user_util';
import AuthConfirmationMessage from '../auth-confirmation-message';
import { getbazaarVoiceSettings } from '../../../../utilities/api/request';

export default class BvAuthConfirmation extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isModelOpen: true,
      isUserVerified: false,
    };
  }

  componentDidMount() {
    const {
      bvAuthToken,
    } = this.props;

    const apiUri = '/data/authenticateuser.json';
    const params = `&authtoken=${bvAuthToken}`;
    const apiData = postAPIData(apiUri, params);
    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined && result.data !== undefined) {
          if (!result.data.HasErrors) {
            const userId = result.data.Authentication.User;
            setSessionCookie('bv_user_id', userId);
            this.setState({
              isUserVerified: true,
            }, () => {
              this.setAnonymousUserCookies();
            });
          }
        } else {
          Drupal.logJavascriptError('review-bv-auth-confirmation', result.error);
        }
      });
    }
  }

  closeModal = () => {
    this.setState({
      isModelOpen: false,
    });
  };

  setAnonymousUserCookies = () => {
    const nicknameKey = getUserNicknameKey();
    const bazaarVoiceSettings = getbazaarVoiceSettings();
    // Store user information in bv cookies.
    if (getSessionCookie('bv_user_id') !== null && getCurrentUserEmail() === null) {
      const params = `&productid=${bazaarVoiceSettings.productid}&User=${getSessionCookie('bv_user_id')}&Action=`;
      const apiData = fetchAPIData('/data/submitreview.json', params);
      if (apiData instanceof Promise) {
        apiData.then((result) => {
          if (result.error === undefined
            && result.data !== undefined
            && result.data.error === undefined) {
            if (result.data.Data.Fields !== undefined
              && result.data.Data.Fields.usernickname.Value !== null
              && result.data.Data.Fields.useremail.Value !== null) {
              setSessionCookie(nicknameKey, result.data.Data.Fields.usernickname.Value);
              setSessionCookie('bv_user_email', result.data.Data.Fields.useremail.Value);
            }
          } else {
            Drupal.logJavascriptError('review-summary', result.error);
          }
        });
      }
    }
  };

  render() {
    const {
      isModelOpen,
      isUserVerified,
    } = this.state;

    return (
      <Popup open={isModelOpen}>
        <div className="write-review-form">
          <div className="title-block">
            <a className="close-modal" onClick={this.closeModal} />
            <div className="review-success-msg">
              <BazaarVoiceMessages />
            </div>
            <AuthConfirmationMessage
              isUserVerified={isUserVerified}
            />
          </div>
        </div>
      </Popup>
    );
  }
}
