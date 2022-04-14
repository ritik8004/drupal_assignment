import React from 'react';

const ConditionalView = ({ condition, children }) => {
  if (condition === undefined || condition === false) {
    return (null);
  }

  return (
    <>
      {children}
    </>
  );
};

export default ConditionalView;
