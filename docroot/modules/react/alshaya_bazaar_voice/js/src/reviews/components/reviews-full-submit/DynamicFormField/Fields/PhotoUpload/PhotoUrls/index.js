import React from 'react';
import { postAPIPhoto, postRequest } from '../../../../../../../utilities/api/request';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../../../../../../../../js/utilities/showRemoveFullScreenLoader';

class PhotoUrls extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      bvPhotoUrl: null,
    };
  }

  /**
   * Get url for photo uploded.
   */
  componentDidMount() {
    const {
      imageDataUrl,
      imageName,
      imageType,
    } = this.props;

    const cleanDataUrl = imageDataUrl.replace(`data:${imageType};base64,`, '');
    const data = {
      dataUrl: cleanDataUrl,
      fileName: imageName,
    };

    // Generate image Url from image Data.
    showFullScreenLoader();
    const fileData = postRequest('/uploadfile', data);
    if (fileData instanceof Promise) {
      fileData.then((result) => {
        if (result.status === 200) {
          if (result.data) {
            const photoUrl = result.data;
            const params = `&contenttype=Review&photourl=${photoUrl}`;
            const apiUri = '/data/uploadphoto.json';
            const apiData = postAPIPhoto(apiUri, params);
            if (apiData instanceof Promise) {
              apiData.then((response) => {
                if (response.error === undefined && response.data !== undefined) {
                  removeFullScreenLoader();
                  if (response.data.Photo !== undefined
                    && response.data.Photo !== null) {
                    const url = response.data.Photo.Sizes.thumbnail.Url;
                    this.setState({ bvPhotoUrl: url });
                  }
                } else {
                  removeFullScreenLoader();
                  Drupal.logJavascriptError('review-bv-photo-urls', response.error);
                }
              });
            }
          }
        } else {
          removeFullScreenLoader();
          Drupal.logJavascriptError('review-photo-urls', result.error);
        }
      });
    }
  }

  render() {
    const { bvPhotoUrl } = this.state;
    const { index } = this.props;

    if (bvPhotoUrl !== null) {
      return (
        <input
          key={index}
          type="text"
          defaultValue={bvPhotoUrl}
          hidden
          name={`photourl_${index + 1}`}
          id={`photourl_${index + 1}`}
        />
      );
    }
    return (null);
  }
}

export default PhotoUrls;
