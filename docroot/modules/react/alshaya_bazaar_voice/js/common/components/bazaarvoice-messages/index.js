import React from 'react';
import getStringMessage from '../../../../../js/utilities/strings';

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

  showMessage = (event) => {
    if (!this.isComponentMounted) {
      return;
    }

    const response = event.detail.data;
    const errorMessages = [];

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
    if (response.data.HasErrors && response.data.FormErrors !== null) {
      if (response.data.FormErrors.FieldErrors !== null) {
        const fieldErrors = response.data.FormErrors.FieldErrors;
        Object.values(fieldErrors).forEach((item) => {
          errorMessages.push(item);
        });
        if (errorMessages && errorMessages.length > 0) {
          this.setState({
            errorList: errorMessages,
          });
          return;
        }
      }
    }
    this.setState({
      message: '',
      errorList: [],
    });
  };

  render() {
    const { message, errorList } = this.state;
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
          <div className="exception-field-error-list">
            {errorList.map((error) => (
              <li key={`error-${error.Field}`}>{error.Message}</li>
            ))}
          </div>
        </div>
        )}
      </>
    );
  }
}
