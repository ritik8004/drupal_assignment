import React from 'react';
import Popup from 'reactjs-popup';

const ReviewPhotos = ({
  photoCollection,
}) => (
  <div className="thumbnail-img-block">
    {Object.keys(photoCollection).map((item) => (
      <Popup
        key={item}
        trigger={(
          <button type="button" className="thumbnail-img">
            <img src={photoCollection[item].Sizes.large.Url} />
          </button>
        )}
        position="bottom center"
        closeOnDocumentClick={false}
      >
        {(close) => (
          <div className="modal">
            <button type="button" className="close" onClick={close} />
            <div className="large-image">
              <img src={photoCollection[item].Sizes.large.Url} />
            </div>
          </div>
        )}
      </Popup>
    ))}
  </div>
);

export default ReviewPhotos;
