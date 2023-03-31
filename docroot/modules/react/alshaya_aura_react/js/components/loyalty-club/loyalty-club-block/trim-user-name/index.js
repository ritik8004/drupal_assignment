import React from 'react';
import { isDesktop, isMobile } from '../../../../../../js/utilities/display';
import { checkStringLength, getAuraConfig } from '../../../../utilities/helper';

const TrimUserName = (props) => {
  const { userName } = props;
  const { bannerUsernameMaxLength } = getAuraConfig();

  let trimName = null;
  if (isDesktop()) {
    trimName = checkStringLength(userName, bannerUsernameMaxLength);
  }

  return (
    <>
      {isDesktop()
        && (
        <div
          title={trimName ? userName : ''}
          className={trimName ? 'trim-user-name' : ''}
        >
            {userName}
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
