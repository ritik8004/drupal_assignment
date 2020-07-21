import React from 'react';

export default class AppointmentMessages extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      message: '',
    };
    this.showMessage = this.showMessage.bind(this);
  }

  componentDidMount() {
    document.addEventListener('showMessage', this.showMessage);
  }

  showMessage(event) {
    const { response } = event.detail.data;
    if (response !== undefined) {
      const { data } = response;
      const { error } = data;
      if (error) {
        this.setState({
          message: Drupal.t('Sorry, something went wrong. Please try again later'),
        });
      }
    } else {
      this.setState({
        message: '',
      });
    }
  }

  render() {
    const { message } = this.state;

    return (
      <>
        { message
        && (
        <div className="error">
          { message }
        </div>
        )}
      </>
    );
  }
}
