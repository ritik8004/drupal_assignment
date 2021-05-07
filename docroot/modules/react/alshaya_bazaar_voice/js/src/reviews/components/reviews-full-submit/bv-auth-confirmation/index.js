import React from 'react';
import Popup from 'reactjs-popup';
import { postAPIData, fetchAPIData } from '../../../../utilities/api/apiData';
import BazaarVoiceMessages from '../../../../common/components/bazaarvoice-messages';
import {
  getCurrentUserStorage,
} from '../../../../utilities/user_util';
import AuthConfirmationMessage from '../auth-confirmation-message';
import { getbazaarVoiceSettings } from '../../../../utilities/api/request';
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
              this.setAnonymousUserCookies(bvUserId);
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

  setAnonymousUserCookies = (bvUserId) => {
    const bazaarVoiceSettings = getbazaarVoiceSettings();
    const userStorage = getStorageInfo('bvuser') !== null ? getStorageInfo('bvuser') : [];
    const currentUserStorage = getCurrentUserStorage(bazaarVoiceSettings.reviews.user.user_id);
    let contentExists = false;
    // Store user information in bv cookies.
    if (currentUserStorage !== null && bazaarVoiceSettings.reviews.user.user_id === 0) {
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
              if (userStorage !== null) {
                const updatedStorage = userStorage.map((contentStorage) => {
                  // Check if current content already exists in storage.
                  if (contentStorage.userId === 0) {
                    const storageObj = { ...contentStorage };
                    storageObj.nickname = result.data.Data.Fields.usernickname.Value;
                    storageObj.email = result.data.Data.Fields.useremail.Value;
                    storageObj.bvUserId = bvUserId;
                    contentExists = true;
                    return storageObj;
                  }
                  return contentStorage;
                });
                if (contentExists) {
                  setStorageInfo(JSON.stringify(updatedStorage), 'bvuser');
                }
              }
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
