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
        <span>{ `(${Drupal.t('@type', { '@type': returnType }, { context: 'online_returns' })}` }</span>
        <span>{ `${Drupal.t('orders can only be returned at stores', {}, { context: 'online_returns' })})` }</span>
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
        { Drupal.t('Return Items Online', {}, { context: 'online_returns' }) }
      </button>
    </>
  );
};

export default ReturnAction;
