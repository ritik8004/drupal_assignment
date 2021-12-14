import React from 'react';

class AuraFormModalMessage extends React.Component {
  constructor(props) {
    super(props);
    this.messageRef = React.createRef();
  }

  render() {
    const {
      messageType,
      messageContent,
    } = this.props;

    if (messageType === null || messageContent === null) {
      return null;
    }

    return (
      <div ref={this.messageRef} className={`aura-message ${messageType}-aura-message`}>
        {messageContent}
      </div>
    );
  }
}

export default AuraFormModalMessage;
