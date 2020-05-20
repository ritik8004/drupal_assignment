import React from 'react';
import parse from 'html-react-parser';
import PdpSectionText from '../utilities/pdp-section-text';

const PdpFullDescription = (props) => {
  const { pdpDescription } = props;

  const CheckItem = (value) => {
    if (value['#items']) {
      const itemList = value['#items'];
      return (
        <>
          <PdpSectionText>
            <ul>
              {
                itemList.map((item) => (
                  <li>{item}</li>
                ))
              }
            </ul>
          </PdpSectionText>
        </>
      );
    }

    return (
      <>
        <PdpSectionText>{parse(value['#markup'])}</PdpSectionText>
      </>
    );
  };
  return (
    <>
      <div className="magv2-desc-popup-description-wrapper">
        {
          pdpDescription.map((item) => (
            <>
              {((item.value['#markup']) || (item.value['#items']))
                ? (
                  <div className="desc-label-text-wrapper">
                    {(item.label) ? (<PdpSectionText className="dark">{parse(item.label['#markup'])}</PdpSectionText>) : null}
                    {CheckItem(item.value)}
                  </div>
                )
                : null}
            </>
          ))
        }
      </div>
    </>
  );
};
export default PdpFullDescription;
