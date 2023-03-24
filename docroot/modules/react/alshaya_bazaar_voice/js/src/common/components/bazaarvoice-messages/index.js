import React from 'react';
import getStringMessage from '../../../../../../js/utilities/strings';
import { getbazaarVoiceSettings } from '../../../utilities/api/request';

export default class BazaarVoiceMessages extends React.Component {
  isComponentMounted = true;

  constructor(props) {
    super(props);
    this.state = {
      message: '',
      errorList: [],
    };
  }

  componentDidMount() {
    this.isComponentMounted = true;
    // Listen to the show Message event.
    document.addEventListener('showMessage', this.showMessage);
  }

  componentWillUnmount() {
    this.isComponentMounted = false;
    document.removeEventListener('showMessage', this.showMessage);
  }

  processErrors = (fieldErrors) => {
    const errorMessages = [];
    try {
      Object.values(fieldErrors).forEach((item) => {
        errorMessages.push(item);
      });
      if (errorMessages.length > 0) {
        this.setState({
          errorList: errorMessages,
        });
        return null;
      }
    } catch (e) { return null; }

    return null;
  };

  showMessage = (event) => {
    if (!this.isComponentMounted) {
      return;
    }

    const response = event.detail.data;

    if (response === undefined) {
      this.setState({
        message: '',
      });
      return;
    }

    if (response.status !== 200) {
      const data = response;
      const error = data;
      if (error) {
        this.setState({
          message: getStringMessage('default_error'),
        });
        return;
      }
    }
    this.setState({
      message: '',
      errorList: [],
    });
    if (response.data.HasErrors && response.data.FormErrors !== null) {
      if (response.data.Errors.length > 0) {
        this.processErrors(response.data.Errors);
      }
      if (response.data.FormErrors.FieldErrors !== null) {
        this.processErrors(response.data.FormErrors.FieldErrors);
      }
    }
  };

  render() {
    const { message, errorList } = this.state;
    const bazaarVoiceSettings = getbazaarVoiceSettings();

    return (
      <>
        { message
        && (
        <div className="exception-error">
          { message }
        </div>
        )}
        { errorList && errorList.length > 0
        && (
        <div className="exception-error">
          <ul className="exception-field-error-list">
            {errorList.map((error) => (
              <li key={`error-${error.Field}`}>{bazaarVoiceSettings.reviews.bazaar_voice.bv_error_messages[error.Code]}</li>
            ))}
          </ul>
        </div>
        )}
      </>
    );
  }
}
