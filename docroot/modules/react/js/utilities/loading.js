import React from 'react';

const Loading = (props) => {
  const { loadingMessage } = props;
  return <div className="loading-container"><div className="loader-icon">{loadingMessage}</div></div>;
};

export default Loading;
