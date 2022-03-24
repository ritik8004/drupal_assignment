import React from 'react';
import { hasValue } from '../../../../js/utilities/conditionsUtility';

const ReturnAction = (props) => {
  const {
    returnType,
    handleOnClick,
  } = props;

  if (hasValue(returnType)) {
    return (
      <div className="return-message">
        <span>{ `(${Drupal.t('@type', { '@type': returnType })}` }</span>
        <span>{ `${Drupal.t('orders can only be returned at stores')})` }</span>
      </div>
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
