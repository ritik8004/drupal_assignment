import React from 'react';

const Loading = (props) => {
  const { loadingMessage } = props;
  return <div className="appointment-loading-container"><div className="appointment-loader">{loadingMessage}</div></div>;
};

export default Loading;
