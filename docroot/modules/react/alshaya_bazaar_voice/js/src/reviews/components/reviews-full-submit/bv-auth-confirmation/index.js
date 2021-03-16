import React from 'react';
import Popup from 'reactjs-popup';
import { postAPIData } from '../../../../utilities/api/apiData';
import BazaarVoiceMessages from '../../../../common/components/bazaarvoice-messages';
import ConditionalView from '../../../../common/components/conditional-view';

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
            this.setState({
              isUserVerified: true,
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
          </div>
          <ConditionalView condition={isUserVerified === true}>
            <div className="auth-confirmation-message">
              <h1>{Drupal.t('Thank you for your contribution.')}</h1>
              <div className="submission-msg">{Drupal.t('We have verified your submission. If the content is approved it will be published within 72 hours.')}</div>
            </div>
          </ConditionalView>
        </div>
      </Popup>
    );
  }
}
