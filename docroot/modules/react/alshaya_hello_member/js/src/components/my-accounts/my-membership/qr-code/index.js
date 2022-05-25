import React from 'react';
import Popup from 'reactjs-popup';
import getStringMessage from '../../../../../../../js/utilities/strings';
import MemberID from '../member-id';

class QrCode extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isModelOpen: false,
    };
  }

  openModal = (e) => {
    e.preventDefault();
    document.body.classList.add('open-form-modal');

    this.setState({
      isModelOpen: true,
    });
  };

  closeModal = (e) => {
    e.preventDefault();
    document.body.classList.remove('open-form-modal');

    this.setState({
      isModelOpen: false,
    });
  };

  render() {
    const { isModelOpen } = this.state;

    return (
      <>
        <div onClick={(e) => this.openModal(e)} className="qr-code-button">
          {getStringMessage('view_qr_code')}
        </div>
        <Popup
          open={isModelOpen}
          className="qr-code-modal"
          closeOnDocumentClick={false}
          closeOnEscape={false}
        >
          <div className="qr-code-block">
            <div className="qr-code-title">
              <span>{getStringMessage('qr_code_title')}</span>
              <a className="close-modal" onClick={(e) => this.closeModal(e)}/>
            </div>
            <div className="qr-img-block">
              <div className="qr-redeem">{getStringMessage('qr_code_redeem')}</div>
              <div className="img-container">
                <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAMgAAADICAYAAACtWK6eAAAAAXNSR0IArs4c6QAADpVJREFUeF7t3cFyI0cMA1Dl/z86KVfl6nldBVGckZErBRINAtNjeXfzz+v1+vf13f/9g+NNn396ftpfeLljWj/NH63/iPPVB3y9XjLA9Pmn56f9hZcBp/XT/NF6AzL/gJABU4Ol/YWXAVP+6r9ab0AakAbkIoINSAPSgDQgl7f09CuCDJjOT/sLr1eclL/6r9Z7g/QGaUB6g/QGgQeSp/Sfv0HSJ0wi/glWCxJ/4U84XH1G89Vf/NL+mq/63flF/E9esbYXEB3wAb8HmT6f+qf1BiRVcBifLkj4lH76gBG/tH96vrvz0/ku+fcG2f8hPVrgwQ2p/mm9AUkVHManCxI+pZ8+4cUv7Z+e7+78dL7eIFBIC5bAqqcGFr+0v/irfnd+Ef++YvUVSwZSvQGRQsv1dEHCp8dLn/Dil/ZPz3d3fjrf+CuWBBJB1WUAzd/Gp+cTXvVUn+n+4qf5qkf7f8cr1q0PePAtj/hHAmt7B/wOWlx+JD2f5qf9hdd81aP9NSD+GSQSWNtrQMb/wl60vwakAZGBlHHdAOovvOarHs1vQBoQGUgGlMHVX3jNVz2a34A0IDKQDCiDq7/wmq96NL8BaUBkIBlQBld/4TVf9Wh+A9KAyEAyoAyu/sJrvurR/AbEARldwMG3WDJQZICD+Tr/ND/NVz3SpwFpQGQgGbABgUISSAKrrgVqfoq/O7/0fMLr/Nv6i5/O1z9qMhzwaAEHrzjTBhR/GXCan+arrvM1IA3IpQIykAzYgAwbTAvQArcXtM1ver72s62/+EX69If0/pAuA8mADUhvkOgVZdpAMng6vwGBAukCJLDq6XzhNX+7nho8xev8aX/hNV917b8/pEvBm9dloMgAB9+iSZ5pfpqveqRPfwaRvPv1aQPKQFJgmp/mq67z9QaRgjevTxtQBpI80/w0X3WdrwGRgjevTxtQBpI80/w0X3WdrwGRgjevTxtQBpI80/w0X3WdrwGRgjevTxtQBpI80/w0X3WdrwGRgjevTxtQBpI80/w0X3WdbzwgIjhdn16QBNb5xE941VN+6q+6zrfNL+L/jq95RWC6ni4oxet86i+86tsG1Pm2+Um/3iBQaHrB6q8Fqr5tQJ1vm5/0a0AaEHkkqjcgkXzz4HRBKV4nVH/hVd9+Qut82/ykX2+Q3iDySFRvQCL55sHpglK8Tqj+wqu+/YTW+bb5Sb/eIL1B5JGo/ucDEql3A7CeYOmCi7/BkqconPweZGr2p/o2INdKTwf8U3semdOA+O+kN2BZwEaM+6mmDUgD0hvkIm0NSAPSgDQglxfytEH++ivap96GRub0BukNMv2AGDHup5o2IA1IA4JXrE+F8alzUgPp3E/vr/M9uq7340cf7k3kn27gaf5vkvmebRoQ72XaYE/vbwUf/IkGxMt7uoGn+VvBB3+iAfHypg329P5W8MGfaEC8vKcbeJq/FXzwJxoQL2/aYE/vbwUf/IkGxMt7uoGn+VvBB3/iHb8oTEOmBabypvzS+Tqf+Akvfml/4TVf/NVfeM2P6g1IJN8RWAueNkjaX3iJsH1+8busNyCRfEfgbYPI4Ck/iZD2F17zo3oDEsl3BNaCUwOLRNpfeM3fPr/49QaJFMrB2waRwVN+UijtL7zmR/XeIJF8R2AtODWwSKT9hdf87fOLX2+QSKEcvG0QGTzlJ4XS/sJrflTvDRLJdwTWglMDi0TaX3jN3z6/+MU3yLZA0QFf/gtRaX/hpd+2gcRP51Nd5xM+5af5l/1PbpBVglLvoC6BDlpEH5F+4pfiRV79hVdd5xM+5af5DYg2MFzXgqMFvuGGFL9UHp1P/VN+mt+AaAPDdS04WmADwu1F+vYVi/rGH2hAMgmln7o3IFBIAkngtK4Fi1+KF3/1F151nU/4lJ/m9xVLGxiua8HRAvuKxe1F+vYVi/rGH2hAMgmln7rHAUkHCK+6BNABp/uLn+an/NV/uz6tj/qn+vIVSwKnBNR/VYCDVxTx0/mm9dP86fq0Puqf6tuAhD/Ea0EyYLpA9d+uT+uj/qm+DUgDMpohGVjDZXD1F17zG5AGRB6J6jKwmsvg6i+85jcgDYg8EtVlYDWXwdVfeM1vQBoQeSSqy8BqLoOrv/Ca34A0IPJIVJeB1VwGV3/hNX88IDqACI4e8OBrXPHT+bb5T/NL9RFe9Wl9L+dL3B+wCJ70uCKh/hJQ87+9//T5U/2FV316fw2INoD6tAHT/ik+lOel+Wn/BiRUUAuaFni7//T5tR7NF171aX17g2gDvUEihRqQ0EBSf/oJ8e39ZdD0/Nqf5guveso/4ncCFsGTHv0h/XcFpF+qv/AyqOriL7zqKf+I3wlYBE96NCANiILwW13+U9/Inz9gEdAA4UcP8AH+6flT/LR+6q+69q/zp/2F1/xL/g3I/AMiWpC2/3qNf80qCg0IFJJAElgGEl7z1f/ueJ1f5xM+raf6ab76Cy99eoOEAY8EPnjCTxtABkrr4i/9NF/9hdf8BqQBkYeiugwsg2q4+guv+Q1IAyIPRXUZWAbVcPUXXvMbkAZEHorqMrAMquHqL7zmNyANiDwU1WVgGVTD1V94zR8PiAimB1R/CSB8yi+dP81P/cVf+qT4lJ/wEf93/B4kJSi86lqQ8BJQ+HS++qf81F/8NT/Fp/yEj/g3IP5FoRYggwivuhYsvOrir/kpPuUnfMS/AWlAUoOneBlc/YVvQKRQ+EO62qcLVH8tWHjVxV/zU3zKT/iIf2+Q3iCpwVO8DK7+wjcgUqg3yKUCMmBksIM/ba31iZ/wEf/eIL1BZMDIYN8QECVQdQkovOrTC9T8p9e1n219p/lpf5fnlzhq/lPXAU96XH1GHDVf+JTf3fGpPile+qT9hdf8BkQKfXldBtIDJMVL3rS/8JrfgEihL6/LQA3IhQEkzol3tICTHn3FSlX6Ha/9yAMpXidL+wuv+b1BpNCX12WgBqQ3yJdH4Pp4Dci1Pr1B/nQ8/C1jb5DhG0T+0xNMeC1QeM1X/xR/d37p+VJ8qo/w2u8lPgKL2f91Cag2KUfNV/8Ur/Ol/Z+OT/URXvttQKCgBEwNqAWm/Z+OT/URXvttQBqQSwW2AyaDi5/wDQgUksASMMVrgWn/p+NTfYTXfnuD9AbpDaIU/VaP0nU4VE84tUk5ar76p3idL+3/dHyqj/Dab2+Q3iC9QZSiqxtETyD1VkLVP8Wn/IRP63c/v/il50/xqT8i/A84FSgi0H/9nP9/j+39pAZP8av+akDS9Rkvg6cGEIPp/pqf1lP+Eb4BSddnfANija4+ERk8fUNpQLLlnaAbkBOVfv9MAwL9ZDDJL4GFT+viL37Ci990f81P6yn/CN8bJF2f8TJ4tECPH/8S4IBC9JFUnwjfgES7OwI3IEcy/fqhyODv+Bkko/98tAysE2qBwmv+dH/xS+er//b5L883fXiJc4e6FiSOqYaaP91/+nzqv33+BuTmXwJsG0QGTgOq/tvnb0AaEHn0st6ARPI9H6wnmE6YGkjzp/tPn0/9t8/fG6Q3iDzaG+Q3BdKnU6T8TcB6golmqqHmT/efPp/6b5+/N0hvEHm0N8jVDaIER+reAJw+gdMjbOur84vfNj7VX3jeIBJIA+5e14Kn+W/rq/OL3zZ+ej8NyLTCw69wKf1tg6fz0/ML34BIoeG6ntDD4+M/rJgaPMWv6vNDfnuBqwJMD7+BvqlBt/HTK+oNMq1wX7Gib8G2H9ANSANyqYAM2htk2UDT47Xg6fky4PR8nV/8tvGr+pz8DCKBpg+g/tsLvjs/6SP+0/tP+aX8L+c3IP6SIjWIDKD+0/jUYMKrrvMJr3qkbwPSgKQGE171BkQKhXUJHD1BDv5Os+hv89N88Zd+wque8lN/8e8rVvg1rATWgmQA9Z/Gi7/4Ca+6zie86uLfgDQg8tBlXQaLmn/gF6ni34A0IJGHZbCoeQOSyme8rmgtOMWLYdp/Gi/+0k941XU+4VUX//Eb5NYHPPgh++78UwMIP31+zZ+uNyBQOBLoDdubnq/+OkIDcqHQO34PMi2wDKD5KV4GU316vvqLn/QT/u516dNXrPCH9NQA0YIOhqu/WjQgvUEuPTJtEBk4na/+Dci1Ar1BeoOsPiAU0Om6HiANSAPSgFwo0IA0IA1IA/K7ArpidcX/9Z8RpJ/0EV76p3Xxu+zfr3ktfyTwwS8qxSCdr/6qy+DiJ7zmp3Xxa0BChSOBGxD+s0PhegiP9tcbhPrG/yxS+gSNFuzj8RPiL37Ck0D4AfHrDbIpcG+Q3iBRQg/MqyeQ5gsvCuov/PZ88VNd/KWP8Jqf1sWvN0iocCRwb5DeIKmB5F89gTRfeM1Xf+G354uf6uIvfYTX/LQufr1BUoWB1wIebZCDG3D6/Gn/CN9vsfL0RAvIx7OD+KmBAq7+wmt+2j/CNyBaj+vRAtw+/oT4aYAMrv7Ca37aP8I3IFqP69EC3D7+hPhpgAyu/sJrfto/wjcgWo/r0QLcPv6E+GmADK7+wmt+2j/CNyBaj+vRAtw+/oT4aYAMrv7Ca37aP8I3IFqP69EC3D7+hPhpgAyu/sJrfto/wjcgWo/r0QLcPv6E+GmADK7+wmt+2j/CvyMgOuB0PRLg7v+y3xf8HkL71/6ET+uXAW5A/L8/GF3AQUD1BJbBhNf5tvuLX1pvQKCgDDC6gAYk/usEo/vpDdIbRAbTA2T6hhK/tN4bpDdI5KEGBPKlT4hoOwfgdIHCH1C4/Ij00/xpvM6X8kv7C5/We4P0Bok81ID0BokMJPD0DbBtYJ1P+oi/8Gk9vkFSAtt4LTBdUNo/xUtf9Rde+qi/8Jq/Wj/5FmuV4BuGTy8w7Z/iJZH6Cy+Dq7/wmr9ab0Dyr3lTg6R4GUj9hZfB1V94zV+tNyANiAwogzcgUvDm9ekFpv1TvORXf+EbECn08LoMIgPo+Gn/FJ/yE176TPMXv9F6X7H6iiWDNSBS6OH16Sdc2j/Faz3qL/yfDsh/uYNgpPt9FYAAAAAASUVORK5CYII="/>
              </div>
            </div>
            <div className="my-membership-id">
              <MemberID />
            </div>
          </div>
        </Popup>
      </>
    );
  }
}

export default QrCode;
