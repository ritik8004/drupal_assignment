import React from 'react';
import BenefitPaySVG from './components/benefit-pay-svg';

const PaymentMethodIcon = (props) => {
  const { methodName, methodLabel } = props;
  if (methodName === 'banktransfer') {
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
  if (methodName === 'checkout_com'
    || methodName === 'checkout_com_upapi') {
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
  if (methodName === 'checkout_com_upapi_knet') {
    return (
      <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="156px" height="99px" viewBox="0 0 156 99" enableBackground="new 0 0 156 99" space="preserve">
        <image
          id="image0"
          width="156"
          height="99"
          x="0"
          y="0"
          href="data:image/png;base64,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
          kCIY9mL95c+/In3n1fKRy8558yfWF5g5I8CMUMoQSTQhSpgDqnDxAkTAHEERglaLtKk5MZaINXcg
          IGALciEIxNhQo20k2raaiiQaXiWEDAdBniV7IEiFQ1sdkAEg2TXjzUAnDVQoTFqqQ1PLIm9/C92X
          tYANyiEQJmPlc4ASDA8E1aonyQ2VmfiPmucYmFLg7ckcIjuQQ8JlIrNb0nJPXu6Y3W/dfmNaIIxT
          5XM7Ia1rgqTDVVWrARgoOfW0GlFdqDb/yqef++3PSjwZcdcC/+i/vfNdtz607gpNKVhq1LQSBNGG
          OiuAFAXwoJztuv+v63G8ghAQLYcK0kPKvE8uXr/88vN+/C9/xcUzsEYsgOPA77936/ffe9PH73t4
          UYuFBNJEGw5HCkJjB276v67/Py6fsq20moTwoqj7V7snHd738uc985XXrO4BVoD/D3GUXOJ12m6S
          AAAAJXRFWHRkYXRlOmNyZWF0ZQAyMDIwLTAzLTIwVDA4OjMwOjQ0KzAzOjAw2qdA9wAAACV0RVh0
          ZGF0ZTptb2RpZnkAMjAyMC0wMy0yMFQwODozMDo0NCswMzowMKv6+EsAAAARdEVYdGV4aWY6Q29s
          b3JTcGFjZQAxD5sCSQAAABd0RVh0ZXhpZjpFeGlmSW1hZ2VMZW5ndGgAOTkJzz6uAAAAF3RFWHRl
          eGlmOkV4aWZJbWFnZVdpZHRoADE1NhPmoGMAAAASdEVYdGV4aWY6RXhpZk9mZnNldAAyNlMbomUA
          AAAASUVORK5CYII="
        />
      </svg>
    );
  }
  if (methodName === 'checkout_com_applepay' || methodName === 'checkout_com_upapi_applepay') {
    return (
      <svg xmlns="http://www.w3.org/2000/svg" xlink="http://www.w3.org/1999/xlink" width="50" height="32" viewBox="0 0 50 32">
        <defs>
          <rect id="prefix__qaa" width="50" height="32" x="0" y="0" rx="2" />
        </defs>
        <g fill="none" fillRule="evenodd">
          <mask id="prefix__qab" fill="#fff">
            <use href="#prefix__qaa" />
          </mask>
          <rect width="49.5" height="31.5" x=".25" y=".25" stroke="#333" strokeWidth=".5" rx="2" />
          <g fill="#333" fillRule="nonzero" mask="url(#prefix__qab)">
            <path d="M16.998 16.084c.02 2.03 1.795 2.704 1.814 2.713-.015.048-.283.962-.935 1.907-.563.817-1.148 1.631-2.07 1.648-.905.017-1.195-.532-2.23-.532s-1.359.515-2.216.549c-.889.033-1.566-.884-2.134-1.698-1.16-1.665-2.048-4.704-.857-6.756.592-1.019 1.65-1.664 2.798-1.68.873-.017 1.697.582 2.23.582.534 0 1.536-.72 2.59-.614.44.018 1.677.176 2.471 1.33-.064.04-1.476.855-1.46 2.551zm-1.701-4.983c.472-.567.79-1.356.703-2.141-.68.027-1.503.45-1.991 1.016-.438.502-.82 1.305-.717 2.074.758.059 1.533-.382 2.005-.95v.001zm6.833-.97c.384-.065.77-.12 1.158-.163.462-.05.927-.075 1.393-.072.723 0 1.347.087 1.872.262.525.175.955.425 1.293.751.292.293.523.644.678 1.031.163.398.244.857.244 1.375 0 .627-.111 1.176-.334 1.647-.215.46-.527.864-.914 1.185-.386.319-.844.557-1.375.714-.53.157-1.11.235-1.737.235-.566 0-1.042-.042-1.428-.126v5.21h-.85V10.13zm.85 6.08c.205.06.434.102.688.125.253.025.524.037.813.037 1.085 0 1.927-.254 2.524-.76.596-.507.895-1.249.895-2.226 0-.47-.078-.88-.235-1.23-.149-.34-.38-.635-.67-.86-.289-.223-.632-.391-1.03-.506-.399-.115-.839-.172-1.321-.172-.386 0-.718.015-.995.045s-.5.064-.67.1v5.446h.001zm12.55 3.908c0 .35.006.7.018 1.049.012.35.048.687.109 1.013h-.796l-.126-1.23h-.037c-.109.169-.247.337-.416.506-.169.169-.365.323-.587.461-.224.14-.477.25-.76.335-.304.087-.617.13-.932.127-.422 0-.793-.07-1.113-.208-.32-.14-.582-.32-.787-.543-.203-.221-.36-.483-.46-.77-.1-.277-.153-.571-.154-.867 0-1.026.43-1.815 1.293-2.37.862-.556 2.162-.821 3.899-.797v-.235c0-.23-.022-.492-.064-.787-.042-.296-.137-.581-.28-.842-.145-.265-.362-.488-.651-.669-.29-.18-.682-.271-1.176-.271-.374 0-.745.057-1.112.172-.368.114-.703.274-1.004.479l-.272-.633c.386-.266.784-.455 1.194-.57.419-.115.85-.173 1.284-.172.603 0 1.095.103 1.475.308.38.205.678.47.895.796.219.33.37.702.443 1.095.078.396.117.799.118 1.203v3.42zm-.85-2.588c-.48-.011-.96.007-1.438.054-.5.048-.962.154-1.384.317-.422.163-.771.4-1.049.714-.277.314-.416.73-.416 1.25 0 .614.175 1.066.525 1.356.35.29.741.434 1.175.434.35 0 .664-.048.94-.145.278-.096.519-.226.724-.388.205-.163.377-.347.516-.552.139-.206.244-.416.317-.634.06-.241.09-.416.09-.525v-1.88zm2.513-4.035L39.4 19.14c.123.32.238.642.345.968.108.331.204.636.289.914h.036c.085-.265.181-.564.29-.896.108-.331.229-.672.361-1.023l2.063-5.608h.904l-2.514 6.369c-.23.615-.469 1.227-.715 1.836-.203.505-.43 1-.678 1.484-.208.404-.44.794-.697 1.167-.226.327-.49.625-.787.886-.337.301-.648.522-.931.66-.284.14-.473.227-.57.263l-.29-.705c.248-.113.49-.24.724-.38.283-.171.545-.378.778-.615.217-.218.455-.503.714-.86.26-.356.492-.787.697-1.294.072-.193.108-.32.108-.38 0-.084-.036-.21-.108-.38l-3.13-8.052h.905z" />
          </g>
        </g>
      </svg>
    );
  }
  if (methodName === 'checkout_com_upapi_qpay') {
    return (
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 152 97" width="152" height="97">
        <defs>
          <image
            id="img1"
            width="152"
            height="97"
            href="data:image/png;base64,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"
          />
        </defs>
        <use id="Background" href="#img1" x="2" y="1" />
      </svg>
    );
  }

  if (methodName === 'postpay') {
    return (
      <svg xmlns="http://www.w3.org/2000/svg" xlink="http://www.w3.org/1999/xlink" width="50" height="32" viewBox="0 0 50 32">
        <defs>
          <rect id="prefix__qa" width="50" height="32" x="0" y="0" rx="2" />
        </defs>
        <g fill="none" fillRule="evenodd" fontFamily="GreycliffCF-Bold, Greycliff CF" fontSize="10" fontWeight="bold">
          <mask id="prefix__qb" fill="#fff">
            <use href="#prefix__qa" />
          </mask>
          <rect width="49.5" height="31.5" x=".25" y=".25" stroke="#333" strokeWidth=".5" rx="2" />
          <g mask="url(#prefix__qb)">
            <g fill="#000">
              <g>
                <g>
                  {
                    drupalSettings.path.currentLanguage === 'en'
                      ? (
                        <text transform="translate(-849 -968) translate(100 734) translate(0 212)">
                          <tspan x="755" y="41">postpay </tspan>
                        </text>
                      )
                      : (
                        <text transform="translate(-810.5 -968) translate(100 734) translate(0 212)">
                          <tspan x="755" y="41">postpay </tspan>
                        </text>
                      )
}
                </g>
              </g>
            </g>
          </g>
        </g>
      </svg>
    );
  }

  if (methodName === 'tabby') {
    return (
      <svg className="styles__tabby-info__logo--ec151" viewBox="0 0 100 40" fill="none" xmlns="http://www.w3.org/2000/svg" width="65px" height="26px" alt={methodLabel}>
        <path d="M92.2524 0H7.74757C3.46871 0 0 3.48347 0 7.78054V32.2195C0 36.5165 3.46871 40 7.74757 40H92.2524C96.5313 40 100 36.5165 100 32.2195V7.78054C100 3.48347 96.5313 0 92.2524 0Z" fill="url(#paint0_linear_99_113_0.9997948959381204)" />
        <path d="M86.9113 12.6893L81.5394 33.1972L81.5274 33.2434H85.7106L91.1016 12.6893H86.9113Z" fill="#292929" />
        <path d="M20.4447 24.3575C19.817 24.6651 19.1268 24.8233 18.4279 24.8198C16.9206 24.8198 16.0655 24.5796 15.9731 23.3607V23.2793C15.9731 23.2301 15.967 23.1788 15.967 23.1306V19.5946L15.9731 19.1765V16.6805H15.967V15.6073L15.9731 15.1863V12.7746L12.237 13.269C14.7673 12.7787 16.2173 10.778 16.2173 8.7864V7.56049H12.015V13.3032L11.7778 13.3695V24.0018C11.9165 26.9892 13.888 28.7648 17.1146 28.7648C18.2708 28.762 19.4146 28.5251 20.4768 28.0684L20.4979 28.0594V24.3294L20.4447 24.3575Z" fill="#292929" />
        <path d="M21.1069 11.9165L9.32401 13.7332V16.7216L21.1069 14.9049V11.9165Z" fill="#292929" />
        <path d="M21.1069 16.2876L9.32401 18.1044V20.9582L21.1069 19.1424V16.2876Z" fill="#292929" />
        <path d="M34.3287 17.6622C34.1619 14.3462 32.0878 12.3767 28.7075 12.3767C26.7652 12.3767 25.1634 13.1273 24.0762 14.5481C22.9889 15.969 22.4202 18.046 22.4202 20.5702C22.4202 23.0944 22.993 25.1784 24.0762 26.5993C25.1594 28.0201 26.7652 28.7698 28.7075 28.7698C32.0878 28.7698 34.1619 26.7932 34.3287 23.4561V28.4573H38.5269V12.7264L34.3287 13.3735V17.6622ZM34.5467 20.5702C34.5467 23.5104 33.0033 25.4095 30.6147 25.4095C28.1519 25.4095 26.6818 23.6008 26.6818 20.5702C26.6818 17.5215 28.1519 15.7007 30.6147 15.7007C31.8125 15.7007 32.8133 16.167 33.5077 17.0502C34.188 17.9134 34.5467 19.1313 34.5467 20.5702Z" fill="#292929" />
        <path d="M50.773 12.3767C47.3917 12.3767 45.3177 14.3452 45.1529 17.6673V8.14732L40.9536 8.79545V28.4523H45.1529V23.4451C45.3177 26.7863 47.3917 28.7648 50.773 28.7648C54.7301 28.7648 57.0925 25.702 57.0925 20.5703C57.0925 15.4385 54.7301 12.3767 50.773 12.3767ZM48.8638 25.4096C46.4743 25.4096 44.9308 23.5105 44.9308 20.5703C44.9308 19.1313 45.2905 17.9134 45.9708 17.0533C46.6652 16.17 47.666 15.7038 48.8638 15.7038C51.3267 15.7038 52.7968 17.5246 52.7968 20.5733C52.7998 23.6009 51.3297 25.4096 48.8668 25.4096H48.8638Z" fill="#292929" />
        <path d="M68.5306 12.3767C65.1493 12.3767 63.0743 14.3452 62.9095 17.6673V8.14732L58.7113 8.79545V28.4523H62.9035V23.4451C63.0683 26.7863 65.1433 28.7648 68.5246 28.7648C72.4807 28.7648 74.8431 25.702 74.8431 20.5703C74.8431 15.4385 72.4817 12.3767 68.5306 12.3767ZM66.6214 25.4096C64.2319 25.4096 62.6885 23.5105 62.6885 20.5703C62.6885 19.1313 63.0472 17.9134 63.7275 17.0533C64.4228 16.17 65.4237 15.7038 66.6214 15.7038C69.0833 15.7038 70.5534 17.5246 70.5534 20.5733C70.5514 23.6009 69.0813 25.4096 66.6214 25.4096Z" fill="#292929" />
        <path d="M74.8531 12.6893H79.3368L82.9794 28.4232H78.96L74.8531 12.6893Z" fill="#292929" />
        <defs>
          <linearGradient id="paint0_linear_99_113_0.9997948959381204" x1="0" y1="19.9995" x2="100" y2="19.9995" gradientUnits="userSpaceOnUse">
            <stop stopColor="#3BFF9D" />
            <stop offset="1" stopColor="#3BFFC8" />
          </linearGradient>
        </defs>
      </svg>
    );
  }

  if (methodName === 'checkout_com_upapi_fawry') {
    return (
      <svg version="1.1" id="fLayer_1" xmlns="http://www.w3.org/2000/svg" xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="170px" height="83px" viewBox="0 0 170 83" enableBackground="new 0 0 170 83" space="preserve">
        <image overflow="visible" width="170" height="83" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAKoAAABTCAMAAAA4NWxhAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTM4IDc5LjE1OTgyNCwgMjAxNi8wOS8xNC0wMTowOTowMSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTcgKFdpbmRvd3MpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjhCREU2NzhEREJCQzExRUFCMTI3REZFRjE3QTFFNkY1IiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjhCREU2NzhFREJCQzExRUFCMTI3REZFRjE3QTFFNkY1Ij4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6OEJERTY3OEJEQkJDMTFFQUIxMjdERkVGMTdBMUU2RjUiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6OEJERTY3OENEQkJDMTFFQUIxMjdERkVGMTdBMUU2RjUiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz7NobVyAAADAFBMVEWlyNlWl7f/6QAAScllkmb/3TkhfqV5m1q91+TJ3OcAWbLr8vYqeYr/4lYAW4y3tjYXcpdyqsQAYqX/3AD/87X/+NgAa5n/0gjk7PMNc53/zQBTinNxl2GirEQAbJcYeKIedY0xfIfT5OyKtsz/6obkyBv/7JIAUoadwtX/1gCTpUvLvCvNwCZOlrY1iq3/++b+1wctgKf/+wC8uC7/2iQAZaH/5gAAaZYAOHRCg33/2QAAVrb/7gD/5AAMbpoAU7zszBSHoVJspsGrsz3/2gD/8a7/4gCTvtL/76IAaZwVbJkAXa7FuizawiLp0xf/1ABWjW4AYZH/30L/8wD/6H3/9MTNvSdioL3/4ACXsGr/3Qr/1gMAXql9ssoAYak3g3n/3QD//PL/0gPdxB43gIO32PgATYLBujJtnLuBoFkAZp//2y3UxCWMpUz/0wT/5WX/zwBEgYXlzBf/1xAAZJL/0QDe6vFKiXT00yzZ5u4AKmpckWjx9vmnrT+Zq0vyzRL1+Pr/2QUAQnv/1QkAVoj/1gw3eqI/hqu20uDhxx0kdpAAW7AVaab/2Rr50g0AZpQMaZcAZKZPhXv60wpZn7yRo1HbzCH4+/z/1yQLZpUNYqsEbJhDjrD9+/5mkG4AT775/f8ibplNi24Na6acrUP+/P0Abpr20w3/2AL+1xsAZ6LUwCf9/v4FbJ2TqUn/1QPVvioAbJgAbpcFaZ4AcJuNutD/4AP/3QH/2wH/2gP//fj8/P35+vsAY6MGaZatzdzw0RIFZ5UHYJAAa5P/////2R7/1AMAbZf/1QEAbZn/1AL/zwn//v8AX49CiHL//f5wkG2epEzQxDf/8xb05BAPaKD8/f753Aju4BOerD4eaoa0sTwGZZP22AkEapz01gdgi3ENYLhjkXjIwC4AWr8RcZT/4itCkbIBbJnP4OrGx6HP5//kxSEhbL/63QDn0jHv8vYAbpX/0QSXq0YAT6CeqET31B4/f4PLyihJgX7+/v7r0hBDgHhHhnnSykP///9PCp9uAAABAHRSTlP///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////8AU/cHJQAADdZJREFUeNrkm3tYE1cWwMOIgCNGSDRKDAOIOkbACsoEkgwgxGgMNgZayTYhjUhjiStmMdFan9W0VbKa4qMPbUu1a5YiNp2WSnT1s4/ZDEGi3Yfb3bXrdrd11X0Wt67V1nXvTBKegfb7LCZ+e/6Amzszl9+ce865554bWHeAjCubs/PnESs755TNoylZd+48MLLdFemyYjyNOl7rug+kfTxALXPdFzLyDmvBoO+hspr5jJitqggwkWmsspDzr20381VPl616aMqUh1aNXK3im8MOW8baGQpUxeePXDD+BBmQE+MXjOTzVeE16hWs1SGmnm8e8zDZTx4eY7Y2ecKIOmcgKlDpivFkCBn/n3+6ayMJVWvmTyNDy2MCubw2clC15qpxg5CeW1P4zTG8asAsCGnR3nNUrfnaeHIwmWxpTHsE76VXT4cBgjBcLpfjENRUe09RhyQlp9s+LPwRpulGqm2Ddm0URC8Gsj56Y5PwnqKqzAsGJyWzUAop/DMmDpIa2vYdhSV2Rgolmfg9ReU/NATpxPctBMVr3CT1q9Xjxm43chCKIHgKBQ9RzsbuIarW/OKtIVDnowCL4rw+1eC/Hasp5FEUpZMoGxqUhYVL7iWqyjxuCNLXDrK9BEF8+PlfMWaR5e5aqKcAevzc2ZlLNsau3AYMQyjW4DiOyd1+u22SicVBcxGCpljbu+3Rgp8yIVhWhFw5hms94l6393u6Pyp/zFBKTTQ6ASnhS3nlL2b6bjxTryAIXTxXigE6EAGuySDMvfnIkoyMHS4MEtd6tFy3W6bBtACmqg2TuWUGuYtuczG3TGZwV7UZQJ9GK4awPYtKFzWJNTK3uDtyV7XJxeCJnrjSG7WdP5RPTXzOB+af8KIV5DQ+rVbsgpJH8OBN0o7A2Pix64fSCho4HL0i9dBGOS5umlRfnRb/EzlQsaZubXx1WuqbeLvLo/l6cVp19aXflZckVFfXL4aw62sTmpVwprga3BI/W+4fTlOXHw86pmpCoVrfODEE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" />
      </svg>
    );
  }

  if (methodName === 'checkout_com_upapi_benefitpay') {
    return (
      <BenefitPaySVG />
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
          <rect id="react-box" width="48" height="32" x=".5" y=".5" stroke="#333" rx="2" />
        </g>
        <path id="cod-svg-text" fill="#000" d="M16.013 19.821a3.943 3.943 0 0 1-1.76.425c-1.242 0-1.902-.786-1.902-2.027 0-1.792 1.258-3.74 3.222-3.74.581 0 1.006.141 1.304.298l.377-1.053c-.235-.125-.88-.33-1.587-.33-2.734 0-4.667 2.389-4.667 4.997 0 1.603.974 2.939 2.939 2.939a5.206 5.206 0 0 0 2.2-.471l-.126-1.038zm7.794 1.336h-1.21c-.016-.456.079-1.178.173-1.964h-.047c-.817 1.571-1.854 2.137-2.923 2.137-1.367 0-2.2-1.021-2.2-2.514 0-2.64 1.949-5.437 5.264-5.437.723 0 1.509.125 2.043.298l-.77 3.96c-.251 1.32-.361 2.672-.33 3.52zm-.817-4.337l.456-2.294c-.189-.063-.472-.11-.912-.11-1.964 0-3.583 2.074-3.583 4.18 0 .848.299 1.665 1.305 1.665 1.1 0 2.357-1.43 2.734-3.441zm2.451 3.991c.362.252 1.085.503 1.902.519 1.603 0 2.907-.911 2.907-2.514 0-.849-.581-1.509-1.43-1.996-.676-.377-1.053-.723-1.053-1.273 0-.644.566-1.147 1.367-1.147.566 0 1.053.189 1.305.346l.377-.99c-.283-.189-.896-.377-1.587-.377-1.603 0-2.75 1.021-2.75 2.341 0 .786.487 1.461 1.367 1.949.785.44 1.053.817 1.053 1.398 0 .676-.55 1.242-1.446 1.242-.629 0-1.273-.252-1.634-.472l-.378.974zm7.465.346l.707-3.803c.314-1.713 1.587-2.875 2.593-2.875.848 0 1.178.534 1.178 1.241 0 .424-.063.77-.11 1.053l-.817 4.384h1.289l.848-4.447c.079-.393.126-.88.126-1.289 0-1.508-1.037-2.042-1.886-2.042-1.163 0-2.074.597-2.718 1.524h-.032L35.027 10h-1.304L31.6 21.157h1.305z" />
      </g>
    </svg>
  );
};

export default PaymentMethodIcon;
