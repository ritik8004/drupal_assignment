import React from 'react';
import { hasValue } from '../../../../js/utilities/conditionsUtility';

const ReturnAction = (props) => {
  const {
    returnType,
    handleOnClick,
  } = props;

  if (hasValue(returnType)) {
    return (
      <span>
        { Drupal.t('@type orders can only be returned at stores', { '@type': returnType }) }
      </span>
    );
  }

  return (
    <>
      <button
        className="return-items-button"
        id="return-items-button"
        type="button"
        onClick={handleOnClick}
      >
        { Drupal.t('Return Items Online') }
      </button>
    </>
  );
};

export default ReturnAction;
