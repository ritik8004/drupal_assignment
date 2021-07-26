import React from 'react';
import { CKEditor } from '@ckeditor/ckeditor5-react';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import { decode } from 'html-entities';
import getStringMessage from '../../../../../../../../../js/utilities/strings';
import ConditionalView from '../../../../../../common/components/conditional-view';
import { getLanguageCode } from '../../../../../../utilities/api/request';

const editorConfiguration = {
  toolbar: ['bold', 'italic', 'bulletedList', 'numberedList', 'undo', 'redo'],
  language: { ui: getLanguageCode(), content: getLanguageCode() },
};

class TextArea extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      data: '',
    };
  }

  handleChange = (e, editor) => {
    const {
      label, id, minLength, maxLength,
    } = this.props;
    this.setState({ data: editor.getData() });

    const htmlString = editor.getData();
    const stripedHtml = htmlString.replace(/<[^>]+>/g, '');
    const decodedStripedHtml = decode(stripedHtml);

    if (decodedStripedHtml.length > 0) {
      if (decodedStripedHtml.length < minLength) {
        document.getElementById(`${id}-error`).innerHTML = getStringMessage('text_min_chars_limit_error', { '%minLength': minLength, '%fieldTitle': label });
      } else if (decodedStripedHtml.length > maxLength) {
        document.getElementById(`${id}-error`).innerHTML = getStringMessage('text_max_chars_limit_error', { '%maxLength': maxLength, '%fieldTitle': label });
      } else {
        document.getElementById(`${id}-error`).innerHTML = '';
      }
    }
  };

  render() {
    const {
      required,
      id,
      label,
      defaultValue,
      maxLength,
      minLength,
      text,
      placeholder,
    } = this.props;

    const { data } = this.state;

    return (
      <>
        <ConditionalView condition={text !== undefined}>
          <div id={`${id}-head-row`} className="head-row">{text}</div>
        </ConditionalView>
        <div id={id} className="write-review-type-textarea">
          <label>
            {label}
            {' '}
            {(required) ? '*' : '' }
          </label>
          <CKEditor
            editor={ClassicEditor}
            config={editorConfiguration}
            onChange={(e, editor) => this.handleChange(e, editor)}
          />
          <textarea
            id={id}
            name={id}
            onChange={(e) => this.handleChange(e)}
            minLength={minLength}
            maxLength={maxLength}
            placeholder={placeholder}
            value={data}
          >
            {defaultValue}
          </textarea>
          <div className="c-input__bar" />
          <div id={`${id}-error`} className={(required) ? 'error' : ''} />
        </div>
      </>
    );
  }
}

export default TextArea;
