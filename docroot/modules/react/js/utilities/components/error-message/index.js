import React from 'react';
import ConditionalView from '../conditional-view';

const ErrorMessage = (props) => {
  const { message } = props;
  const condition = (typeof message !== 'undefined') && (message !== '') && (message !== null);

  return (
    <ConditionalView condition={condition}>
      <div className="error-message">{message}</div>
    </ConditionalView>
  );
};

export default ErrorMessage;
