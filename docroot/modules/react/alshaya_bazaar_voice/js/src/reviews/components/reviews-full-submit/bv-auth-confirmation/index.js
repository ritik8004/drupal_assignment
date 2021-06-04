import React from 'react';
import Popup from 'reactjs-popup';
import { postAPIData, fetchAPIData } from '../../../../utilities/api/apiData';
import BazaarVoiceMessages from '../../../../common/components/bazaarvoice-messages';
import AuthConfirmationMessage from '../auth-confirmation-message';
import { getbazaarVoiceSettings, getUserDetails } from '../../../../utilities/api/request';
import { setStorageInfo, getStorageInfo } from '../../../../utilities/storage';

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
            const bvUserId = result.data.Authentication.User;
            this.setState({
              isUserVerified: true,
            }, () => {
              this.setAnonymousUserStorage(bvUserId);
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

  setAnonymousUserStorage = (bvUserId) => {
    const bazaarVoiceSettings = getbazaarVoiceSettings();
    const userDetails = getUserDetails();
    const userStorage = getStorageInfo(`bvuser_${userDetails.user.webUserID}`);
    // Store user information in bv cookies.
    if (userStorage !== null && userDetails.user.webUserID === 0) {
      const params = `&productid=${bazaarVoiceSettings.productid}&User=${bvUserId}&Action=`;
      const apiData = fetchAPIData('/data/submitreview.json', params);
      if (apiData instanceof Promise) {
        apiData.then((result) => {
          if (result.error === undefined
            && result.data !== undefined
            && result.data.error === undefined) {
            if (result.data.Data.Fields !== undefined
              && result.data.Data.Fields.usernickname.Value !== null
              && result.data.Data.Fields.useremail.Value !== null) {
              userStorage.bvUserId = bvUserId;
              userStorage.nickname = result.data.Data.Fields.usernickname.Value;
              userStorage.email = result.data.Data.Fields.useremail.Value;
              setStorageInfo(userStorage, `bvuser_${userDetails.user.webUserID}`);
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
