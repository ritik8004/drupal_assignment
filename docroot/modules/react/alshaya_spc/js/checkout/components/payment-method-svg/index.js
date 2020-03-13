import React from 'react';

export default class PaymentMethodIcon extends React.Component {
  render() {
    if (this.props.methodName === 'banktransfer') {
      return (
        <svg xmlns="http://www.w3.org/2000/svg" xlink="http://www.w3.org/1999/xlink" width="50" height="32" viewBox="0 0 50 32">
          <defs>
            <rect id="prefix__xa" width="50" height="32" x="0" y="0" rx="2" />
            <path id="prefix__xc" d="M22.851 26.262V10.865c0-.532-.435-.966-.966-.966H7.715c-.532 0-.967.434-.967.966v15.397c0 .531.435.966.967.966h14.17c.531 0 .966-.435.966-.966zm1.38 0c0 1.303-1.055 2.358-2.346 2.358H7.715c-1.291 0-2.347-1.055-2.347-2.358V10.865c0-1.304 1.055-2.358 2.347-2.358h14.17c1.291 0 2.347 1.054 2.347 2.358v15.397z" />
          </defs>
          <g fill="none" fillRule="evenodd">
            <mask id="prefix__xb" fill="#fff">
              <use href="#prefix__xa" />
            </mask>
            <rect width="49.5" height="31.5" x=".25" y=".25" stroke="#333" strokeWidth=".5" rx="2" />
            <g mask="url(#prefix__xb)">
              <g>
                <g transform="translate(6.25 -2.56)">
                  <mask id="prefix__xd" fill="#fff">
                    <use href="#prefix__xc" />
                  </mask>
                  <use fill="#000" fillRule="nonzero" href="#prefix__xc" />
                  <g fill="#000" mask="url(#prefix__xd)">
                    <path d="M0 0H36.808V37.12H0z" transform="rotate(-180 18.404 18.56)" />
                  </g>
                </g>
                <path fill="#FFF" d="M21.471 14.693H26.839V22.426H21.471z" transform="translate(6.25 -2.56)" />
                <path fill="#000" d="M19.938 19.268c-.395 0-.714-.317-.714-.708 0-.39.32-.708.714-.708H30.17c.394 0 .714.317.714.708 0 .39-.32.708-.714.708H19.938z" transform="translate(6.25 -2.56)" />
                <path fill="#000" d="M30.483 18.56l-3.461-3.432c-.28-.276-.28-.724 0-1 .278-.277.73-.277 1.01 0l3.966 3.932c.278.276.278.724 0 1l-3.967 3.933c-.279.276-.73.276-1.01 0-.278-.277-.278-.725 0-1.001l3.462-3.432z" transform="translate(6.25 -2.56)" />
                <path fill="#000" fillRule="nonzero" d="M12.48 14.075c0-.279.23-.5.506-.5s.495.221.495.5v.255c.678.081 1.254.301 1.783.638.184.104.345.278.345.557 0 .36-.288.638-.644.638-.115 0-.23-.035-.345-.105-.403-.243-.794-.417-1.185-.51v2.227c1.748.44 2.496 1.148 2.496 2.39 0 1.276-.99 2.122-2.45 2.262v.696c0 .278-.219.498-.495.498s-.506-.22-.506-.498v-.72c-.863-.092-1.656-.406-2.358-.904-.195-.128-.31-.314-.31-.557 0-.36.276-.638.632-.638.138 0 .276.046.38.127.54.395 1.07.662 1.702.778v-2.286c-1.68-.44-2.461-1.078-2.461-2.39 0-1.24.977-2.099 2.415-2.215v-.243zm2.094 6.17c0-.51-.254-.823-1.14-1.101v2.111c.737-.081 1.14-.452 1.14-1.01zm-3.152-3.792c0 .487.218.788 1.104 1.078v-2.053c-.736.07-1.104.464-1.104.975z" transform="translate(6.25 -2.56)" />
              </g>
            </g>
          </g>
        </svg>
      );
    }
    if (this.props.methodName === 'checkout_com') {
      return (
        <svg xmlns="http://www.w3.org/2000/svg" xlink="http://www.w3.org/1999/xlink" width="50" height="32" viewBox="0 0 50 32">
          <defs>
            <rect id="prefix__qa" width="50" height="32" x="0" y="0" rx="2" />
            <path id="prefix__qc" d="M7.312 14.69c.408 0 .739.331.739.74 0 .407-.331.738-.74.738h-.48v10.928c0 .02.019.04.041.04h18.522v-.793c0-.404.327-.731.731-.731.404 0 .732.327.732.731v1.531c0 .409-.328.74-.732.74H6.872c-.829 0-1.504-.68-1.504-1.518V15.429c0-.408.327-.739.732-.739h1.212zm22.664-6.183c.809 0 1.464.661 1.464 1.478V22.43c0 .816-.655 1.478-1.464 1.478H11.491c-.808 0-1.463-.662-1.463-1.478V9.985c0-.817.655-1.478 1.463-1.478h18.485zm0 7H11.49v6.923h18.485v-6.923zm0-5.522H11.491v2.41h18.485v-2.41z" />
          </defs>
          <g fill="none" fillRule="evenodd">
            <mask id="prefix__qb" fill="#fff">
              <use href="#prefix__qa" />
            </mask>
            <rect width="49.5" height="31.5" x=".25" y=".25" stroke="#333" strokeWidth=".5" rx="2" />
            <g mask="url(#prefix__qb)">
              <g transform="translate(6.25 -2.56)">
                <path d="M0 0H36.808V37.12H0z" />
                <mask id="prefix__qd" fill="#fff">
                  <use href="#prefix__qc" />
                </mask>
                <use fill="#000" fillRule="nonzero" href="#prefix__qc" />
                <g fill="#000" mask="url(#prefix__qd)">
                  <path d="M0 0H36.808V37.12H0z" transform="rotate(-180 18.404 18.56)" />
                </g>
              </g>
            </g>
          </g>
        </svg>
      );
    }

    return (
      <svg xmlns="http://www.w3.org/2000/svg" xlink="http://www.w3.org/1999/xlink" width="49" height="33" viewBox="0 0 49 33">
        <defs>
          <rect id="a" width="49" height="33" rx="2" />
        </defs>
        <g fill="none" fillRule="evenodd">
          <g>
            <use fill="#FFF" href="#a" />
            <rect width="48" height="32" x=".5" y=".5" stroke="#333" rx="2" />
          </g>
          <path fill="#000" d="M16.013 19.821a3.943 3.943 0 0 1-1.76.425c-1.242 0-1.902-.786-1.902-2.027 0-1.792 1.258-3.74 3.222-3.74.581 0 1.006.141 1.304.298l.377-1.053c-.235-.125-.88-.33-1.587-.33-2.734 0-4.667 2.389-4.667 4.997 0 1.603.974 2.939 2.939 2.939a5.206 5.206 0 0 0 2.2-.471l-.126-1.038zm7.794 1.336h-1.21c-.016-.456.079-1.178.173-1.964h-.047c-.817 1.571-1.854 2.137-2.923 2.137-1.367 0-2.2-1.021-2.2-2.514 0-2.64 1.949-5.437 5.264-5.437.723 0 1.509.125 2.043.298l-.77 3.96c-.251 1.32-.361 2.672-.33 3.52zm-.817-4.337l.456-2.294c-.189-.063-.472-.11-.912-.11-1.964 0-3.583 2.074-3.583 4.18 0 .848.299 1.665 1.305 1.665 1.1 0 2.357-1.43 2.734-3.441zm2.451 3.991c.362.252 1.085.503 1.902.519 1.603 0 2.907-.911 2.907-2.514 0-.849-.581-1.509-1.43-1.996-.676-.377-1.053-.723-1.053-1.273 0-.644.566-1.147 1.367-1.147.566 0 1.053.189 1.305.346l.377-.99c-.283-.189-.896-.377-1.587-.377-1.603 0-2.75 1.021-2.75 2.341 0 .786.487 1.461 1.367 1.949.785.44 1.053.817 1.053 1.398 0 .676-.55 1.242-1.446 1.242-.629 0-1.273-.252-1.634-.472l-.378.974zm7.465.346l.707-3.803c.314-1.713 1.587-2.875 2.593-2.875.848 0 1.178.534 1.178 1.241 0 .424-.063.77-.11 1.053l-.817 4.384h1.289l.848-4.447c.079-.393.126-.88.126-1.289 0-1.508-1.037-2.042-1.886-2.042-1.163 0-2.074.597-2.718 1.524h-.032L35.027 10h-1.304L31.6 21.157h1.305z" />
        </g>
      </svg>
    );
  }
}
