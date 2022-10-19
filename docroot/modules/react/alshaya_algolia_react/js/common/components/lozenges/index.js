import React from 'react';
import ImageElement
  from '../../../src/components/gallery/imageHelper/ImageElement';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

// Supported label positions.
const ALLOWED_POSITIONS = ['top-left', 'top-right', 'bottom-left', 'bottom-right'];

const Lozenges = ({ labels, sku, greenLeaf }) => {
  if (typeof labels === 'undefined' || labels.length === 0) {
    return (null);
  }

  if (!(labels && Array.isArray(labels) && labels.length)) { return null; }

  const bifercatedLabels = labels.reduce((aggregator, item) => {
    const currentAggregation = { ...aggregator };
    const currentItem = { ...item };
    currentItem.position = currentItem.position || 'top-left';
    if (!(currentItem.position in currentAggregation)) {
      currentAggregation[currentItem.position] = [];
    }
    currentAggregation[currentItem.position].push(currentItem);
    return currentAggregation;
  }, {});

  const bifercatedLabelsList = Object.keys(bifercatedLabels);

  if (bifercatedLabelsList && Array.isArray(bifercatedLabelsList) && bifercatedLabelsList.length) {
    return (
      <>
        <div className="product-labels">
          <div className="labels-wrapper" data-type="plp" data-sku={sku} data-main-sku={sku}>
            {
              bifercatedLabelsList.map((key) => {
                // Only render labels in supported positions.
                if (ALLOWED_POSITIONS.includes(key)) {
                  return (
                    <div key={`${key}-label-container`}>
                      <div className={`labels-container ${key}`}>
                        <LabelItems
                          bifercatedLabels={bifercatedLabels}
                          directionKey={key}
                          sku={sku}
                        />
                      </div>
                      {hasValue(greenLeaf) && greenLeaf
                        && (
                          <div className="labels-container bottom-right">
                            <span className="map-green-leaf" />
                          </div>
                        )}
                    </div>
                  );
                }
                return null;
              })
            }
          </div>
        </div>
      </>
    );
  }
  return null;
};

const LabelItems = ({ bifercatedLabels, directionKey, sku }) => (
  <>
    {
      bifercatedLabels[directionKey].map((labelItem) => (
        // Product SKU added as a unique key.
        <div className="label" key={`${sku}-label`}>
          <ImageElement
            src={labelItem.image.url}
            alt={labelItem.image.alt}
            title={labelItem.image.title}
            loading="lazy"
          />
        </div>
      ))
    }
  </>
);

export default Lozenges;
