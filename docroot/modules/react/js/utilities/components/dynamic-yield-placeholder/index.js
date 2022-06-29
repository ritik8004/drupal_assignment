import React from 'react';

const DynamicYieldPlaceholder = ({ context, placeHolderCount }) => {
  // Get dynamic yield empty divs count for cart page if dynamic yield enabled.
  if (placeHolderCount <= 0) {
    return null;
  }
  const dynamicYieldDivs = [];
  if (placeHolderCount > 0) {
    for (let index = 0; index < placeHolderCount; index++) {
      const divId = `dy-recommendation-${context}-${index}`;
      dynamicYieldDivs.push(<div key={divId} id={divId} />);
    }
  }

  return (
    <>
      {dynamicYieldDivs}
    </>
  );
};

export default DynamicYieldPlaceholder;
