import React from 'react';
import getStringMessage from '../../../../../js/utilities/strings';

export default class AppointmentMessages extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      message: '',
    };
  }

  componentDidMount() {
    document.addEventListener('showMessage', this.showMessage);
  }

  showMessage = (event) => {
    const { response } = event.detail.data;
    if (response !== undefined) {
      const { data } = response;
      const { error } = data;
      if (error) {
        this.setState({
          message: getStringMessage('default_error'),
        });
      }
    } else {
      this.setState({
        message: '',
      });
    }
  };

  render() {
    const { message } = this.state;

    return (
      <>
        { message
        && (
        <div className="exception-error">
          { message }
        </div>
        )}
      </>
    );
  }
}
