import React from 'react';
import { hasValue } from '../../../../js/utilities/conditionsUtility';

const ReturnWindow = (props) => {
  const { message, closed } = props;

  return (
    <span className={`${hasValue(closed) ? 'return-window-closed' : ''}`}>
      {message}
    </span>
  );
};

export default ReturnWindow;
