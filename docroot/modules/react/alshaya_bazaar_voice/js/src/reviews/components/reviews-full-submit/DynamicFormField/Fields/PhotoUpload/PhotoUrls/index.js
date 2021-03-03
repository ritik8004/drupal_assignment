import React from 'react';
import { postAPIData } from '../../../../../../../utilities/api/apiData';
import { postRequest } from '../../../../../../../utilities/api/request';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../../../../../../../../js/utilities/showRemoveFullScreenLoader';
import BazaarVoiceMessages from '../../../../../../../common/components/bazaarvoice-messages';

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
        if (result.status === 200 && result.statusText === 'OK') {
          if (result.data) {
            // const photoUrl = result.data;
            const photoUrl = 'https://kw.hm.com/sites/g/files/hm/styles/product_zoom_large_800x800/brand/assets-shared/HNM/11902571/83fad35eb48fd3fde86ca5cf12849ff54ac2d11b/2/a43a93661bb4833a8b015fa1c6be2da70f3aa1b1.jpg';
            const params = `&contenttype=Review&photourl=${photoUrl}`;
            const apiUri = '/data/uploadphoto.json';
            const apiData = postAPIData(apiUri, params);
            if (apiData instanceof Promise) {
              apiData.then((response) => {
                if (response.error === undefined && response.data !== undefined) {
                  removeFullScreenLoader();
                  if (response.data.Photo !== undefined
                    && response.data.Photo !== null) {
                    const url = response.data.Photo.Sizes.thumbnail.Url;
                    // Save uploaded image url.
                    this.setState({ bvPhotoUrl: url });
                  }
                } else {
                  removeFullScreenLoader();
                  Drupal.logJavascriptError('bv-photo-urls', result.error);
                }
              });
            }
          }
        } else {
          removeFullScreenLoader();
          Drupal.logJavascriptError('photo-urls', result.error);
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
    return (<BazaarVoiceMessages />);
  }
}

export default PhotoUrls;
