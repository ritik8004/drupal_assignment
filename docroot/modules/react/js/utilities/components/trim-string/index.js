import React from 'react';
import { hasValue } from '../../conditionsUtility';
import { isDesktop, isMobile } from '../../display';

const TrimString = (props) => {
  const {
    stringToTrim,
    desktopCharacterLimit,
    mobileCharacterLimit,
    showEllipsis,
  } = props;

  if (!hasValue(stringToTrim)) {
    return null;
  }

  const ellipsisText = (trimedStr) => (showEllipsis ? `${trimedStr}...` : trimedStr);

  const trimStr = (originalStr, strLength) => (
    originalStr.length > strLength
      // Here we reduce characterLimit by 3.
      // So that the total length of the string match the characterLimit supplied from config.
      ? ellipsisText(originalStr.substring(0, (strLength - 3)))
      : originalStr
  );

  const resultedStr = () => {
    if (hasValue(desktopCharacterLimit) && isDesktop()) {
      return trimStr(stringToTrim, parseInt(desktopCharacterLimit, 10));
    } if (hasValue(mobileCharacterLimit) && isMobile()) {
      return trimStr(stringToTrim, parseInt(mobileCharacterLimit, 10));
    }
    return stringToTrim;
  };

  return (
    <>
      {resultedStr()}
    </>
  );
};

export default TrimString;
