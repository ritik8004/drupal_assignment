import React from 'react';

const ConditionalView = ({ condition, children }) => {
  if (!condition) {
    return (null);
  }

  return (
    <>
      {children}
    </>
  );
};

export default ConditionalView;
