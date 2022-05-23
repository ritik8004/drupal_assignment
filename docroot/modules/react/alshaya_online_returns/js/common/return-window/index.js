import React from 'react';
import { hasValue } from '../../../../js/utilities/conditionsUtility';

const ReturnWindow = (props) => {
  const { message, closed } = props;
  const className = hasValue(closed) ? 'return-window-closed' : '';

  return (
    <span className={className}>
      {message}
    </span>
  );
};

export default ReturnWindow;
