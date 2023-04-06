import React from 'react';
import EllipsisText from 'react-ellipsis-text';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import { isDesktop, isMobile } from '../../../../../../js/utilities/display';

const TrimUserName = (props) => {
  const {
    userName,
    characterLimit,
  } = props;

  if (!hasValue(userName) || !hasValue(characterLimit)) {
    return null;
  }

  return (
    <>
      {isDesktop()
        && (
          <div title={userName}>
            <EllipsisText
              text={userName}
              length={parseInt(characterLimit, 10)}
            />
          </div>
        )}
      {isMobile()
        && (
          <>{userName}</>
        )}
    </>
  );
};

export default TrimUserName;
