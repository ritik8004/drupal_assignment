import React from 'react';
import ImageUploading from 'react-images-uploading';
import TempPhotoUpload from './TempPhotoUpload';

const PhotoUpload = (props) => {
  const { fieldProperty: photoField } = props;
  const [images, setImages] = React.useState([]);
  const maxNumber = 5;

  const onChange = (imageList) => {
    setImages(imageList);
  };

  return (
    <div className="write-review-type-photo-upload">
      <ImageUploading
        multiple
        value={images}
        onChange={onChange}
        maxNumber={maxNumber}
        dataURLKey="data_url"
      >
        {({
          imageList,
          onImageUpload,
          onImageRemoveAll,
          onImageUpdate,
          onImageRemove,
          isDragging,
          dragProps,
        }) => (
          // write your building UI
          <div className="upload__image-wrapper">
            <div className="help-text">{photoField.text}</div>
            <button
              type="button"
              style={isDragging ? { color: 'red' } : null}
              onClick={onImageUpload}
              {...dragProps}
            >
              Click or Drop here
            </button>
            &nbsp;
            <button type="button" onClick={onImageRemoveAll}>Remove all images</button>
            {imageList.map((image, index) => (
              <div key={image[index]} className="image-item">
                <img src={image.data_url} alt="" width="100" />
                <div className="image-item__btn-wrapper">
                  <button type="button" onClick={() => onImageUpdate(index)}>Update</button>
                  <button type="button" onClick={() => onImageRemove(index)}>Remove</button>
                </div>
              </div>
            ))}
            {imageList !== undefined && imageList.length > 0
              && (
                <TempPhotoUpload imageList={imageList} />
              )}
          </div>
        )}
      </ImageUploading>
    </div>
  );
};

export default PhotoUpload;
