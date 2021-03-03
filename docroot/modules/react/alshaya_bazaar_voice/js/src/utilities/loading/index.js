import React from 'react';

const Loading = (props) => {
  const { loadingMessage } = props;
  return <div className="pdp-review-loading-container"><div className="pdp-review-loader">{loadingMessage}</div></div>;
};

export default Loading;
