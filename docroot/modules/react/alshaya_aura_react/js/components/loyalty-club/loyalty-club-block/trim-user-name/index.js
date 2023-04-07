import React from 'react';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import { isDesktop } from '../../../../../../js/utilities/display';
import { truncate } from '../../../../../../js/utilities/helper';

const TrimUserName = (props) => {
  const {
    userName,
    characterLimit,
  } = props;

  if (!hasValue(userName)) {
    return null;
  }

  const trimedUserName = truncate(userName, parseInt(characterLimit, 10));

  return (
    <div title={userName}>
      {hasValue(characterLimit) && isDesktop() ? (
        trimedUserName
      ) : (<>{trimedUserName}</>)}
    </div>
  );
};

export default TrimUserName;
