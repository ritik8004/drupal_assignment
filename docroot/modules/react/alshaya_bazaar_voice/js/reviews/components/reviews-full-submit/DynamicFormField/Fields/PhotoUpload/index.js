import React from 'react';
import ImageUploading from 'react-images-uploading';
import PhotoUrls from './PhotoUrls';

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
          onImageRemove,
          dragProps,
        }) => (
          <div className="upload__image-wrapper">
            <div className="help-text">{photoField.text}</div>
            <div className="image-wrapper">
              {imageList.map((image, index) => (
                <div key={index.toString()} className="image-item">
                  <img src={image.data_url} alt="" imageindex={index} />
                  <PhotoUrls
                    imageDataUrl={image.data_url}
                    imageName={image.file.name}
                    imageType={image.file.type}
                    index={index}
                  />
                  <div className="image-item__btn-wrapper">
                    <button type="button" onClick={() => onImageRemove(index)} />
                  </div>
                </div>
              ))}
            </div>
            <div className="photo-upload-block">
              <div className="user-pic-label">{Drupal.t('Show us how it looks! Upload up to 5 pics ')}</div>
              {imageList.length < maxNumber
                && (
                  <button
                    type="button"
                    onClick={onImageUpload}
                    {...dragProps}
                  >
                    {Drupal.t('Upload a Photo')}
                  </button>
                )}
            </div>
          </div>
        )}
      </ImageUploading>
    </div>
  );
};

export default PhotoUpload;
