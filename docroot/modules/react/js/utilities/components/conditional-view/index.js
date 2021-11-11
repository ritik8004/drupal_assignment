import React from 'react';

const ConditionalView = ({ condition, children }) => {
  if (condition === false) {
    return (null);
  }

  return (
    <>
      {children}
    </>
  );
};

export default ConditionalView;
