import React from 'react';

class Loading extends React.Component {
  render() {
    return <div className="spc-loading-container"><div className="spc-loader">{this.props.loadingMessage}</div></div>;
  }
}

export default Loading;
