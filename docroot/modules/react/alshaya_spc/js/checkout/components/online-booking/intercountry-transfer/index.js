import React from 'react';
import parse from 'html-react-parser';
import moment from 'moment-timezone';

const IntercountryTransfer = (props) => {
  const { ictData } = props;
  const { path } = window.drupalSettings;
  return (
    <>
      <div id="intercountry-transfer" className="intercountry-transfer">
        <label className="radio-sim radio-label">
          <span className="carrier-title">
            {
              parse(
                Drupal.t(
                  'Expected Delivery on !date',
                  {
                    '!date': `${moment(ictData.date).format('Do')} ${moment(ictData.date).locale(path.currentLanguage).format('MMM')} ${moment().format('YYYY')}`,
                  }, { context: 'ict' },
                ),
              )
            }
          </span>
        </label>
      </div>
    </>
  );
};
export default IntercountryTransfer;
