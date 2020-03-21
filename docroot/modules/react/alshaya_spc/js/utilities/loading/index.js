import React from 'react';

const Loading = (props) => {
  const { loadingMessage } = props;
  return <div className="spc-loading-container"><div className="spc-loader">{loadingMessage}</div></div>;
};

export default Loading;
