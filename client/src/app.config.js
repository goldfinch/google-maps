export default function initCfg(command, mode, ssrBuild) {

  const dev = command === 'serve';
  const host = 'silverstripe.lh';

  const buildAssetsDir = '../../../dist/jsoneditor/assets/'

  const jsoneditor = dev ? '../../../dist/jsoneditor/assets/jsoneditor/' : ''
  const jsoneditor_images = dev ? './images/' : (buildAssetsDir + 'jsoneditor/images/');

  return {

    host: host,
    certs: '/Applications/MAMP/Library/OpenSSL/certs/' + host,

    sassAdditionalData: `
      $jsoneditor: '${jsoneditor}';
      $jsoneditor_images: '${jsoneditor_images}';
    `,
  }
}
