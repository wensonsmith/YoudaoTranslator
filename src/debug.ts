declare var tjs

import Translator from './translator';

const main = async () => {
  const translator = new Translator('7ba4d4a34fa33db2', 'cufKMDBAKcvMskaL1XsilrF2gSLa3MsO');

  const result = await translator.translate('你好');

  console.log(result);
}

main();