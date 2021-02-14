import React from 'react';
import { postFile } from '../../../../../../../utilities/api/formData';
import { postAPICall } from '../../../../../../../utilities/api/apiData';

class TempPhotoUpload extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
    // bvResponse: '',
    };
  }

  /**
   * Get url for photo uploded.
   */
  componentDidMount() {
    const {
      imageList,
    } = this.props;

    imageList.forEach((imageData) => {
      const cleanDataUrl = imageData.data_url.replace(`data:${imageData.file.type};base64,`, '');
      const data = {
        dataUrl: cleanDataUrl,
        fileName: imageData.file.name,
      };

      // Generate image Url from image Data.
      const fileData = postFile('/uploadfile', data);
      if (fileData instanceof Promise) {
        fileData.then((result) => {
          if (result.status === 200 && result.statusText === 'OK') {
            if (result.data) {
              const photoUrl = 'https://kw.hm.com/sites/g/files/hm/styles/product_zoom_large_800x800/brand/assets-shared/HNM/12312769/102460c245c9c0326ba3b0bbe18222b22a98bc12/1/102460c245c9c0326ba3b0bbe18222b22a98bc12.jpg';
              const params = `&contenttype=Review&photourl=${photoUrl}`;
              const apiUri = '/data/uploadphoto.json';
              const apiData = postAPICall(apiUri, params);
              if (apiData instanceof Promise) {
                apiData.then((response) => {
                  if (response.error === undefined && response.data !== undefined) {
                    // console.log(response.data);
                    this.setState({
                    // bvResponse: response.data,
                    });
                  } else {
                    // Todo
                  }
                });
              }
            }
          } else {
            // console.log(result);
          }
        });
      }
    });
  }

  render() {
    // const { bvResponse } = this.state;
    // console.log(bvResponse);

    return (
      <div className="temp-photo-upload" />
    );
  }
}

export default TempPhotoUpload;
