import React from 'react';
import ConditionalView from '../conditional-view';

class NotificationMessage extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      message: null,
    };
  }

  componentDidMount() {
    document.addEventListener('showNotificationMessage', this.handleShowNotificationMessage, false);
  }

  handleShowNotificationMessage = (data) => {
    const { message } = data.detail;

    if (typeof message !== 'undefined'
     && message !== ''
     && message !== null) {
      this.setState({ message });
    }
  }

  render() {
    const { message } = this.state;
    const condition = (typeof message !== 'undefined') && (message !== '') && (message !== null);

    return (
      <ConditionalView condition={condition}>
        <div className="notification-container">
          <div className="notification notification-message">{message}</div>
        </div>
      </ConditionalView>
    );
  }
}

export default NotificationMessage;
