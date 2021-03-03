import React from 'react';
import Popup from 'reactjs-popup';
import { postAPIData } from '../api/apiData';

export default class BvAuthConfirmation extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isModelOpen: true,
      isUserVerified: false,
      errors: '',
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
          if (result.data.HasErrors === true) {
            this.setState({
              errors: result.data.Errors,
            });
          } else {
            this.setState({
              isUserVerified: true,
            });
          }
        } else {
          Drupal.logJavascriptError('bv-auth-confirmation', result.error);
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
      errors,
    } = this.state;

    return (
      <Popup open={isModelOpen}>
        <div className="modal">
          <a className="close" onClick={this.closeModal}>
            &times;
          </a>
          {isUserVerified === true
            && (
              <div className="auth-confirmation-message">
                <h1>{Drupal.t('Thank you for your contribution.')}</h1>
                <div>{Drupal.t('We have verified your submission. If the content is approved it will be published within 72 hours.')}</div>
              </div>
            )}
          {errors !== ''
            && (
              <ul>
                {
              Object.keys(errors).map((index, item) => <li key={index}>{errors[item].Message}</li>)
                }
              </ul>
            )}
        </div>
      </Popup>
    );
  }
}
