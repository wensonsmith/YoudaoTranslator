declare var tjs;

import Translator from "./translator";

const debounce = (fn, wait) => {
  let timer: any = null;
  return function () {
    if (timer !== null) {
      clearTimeout(timer);
    }
    timer = setTimeout(fn, wait);
  };
};

const main = debounce(async () => {
  const translator = new Translator(tjs.getenv("key"), tjs.getenv("secret"), tjs.getenv("platform"));

  const word: string = Array.from(tjs.args).pop() as string;

  const result = await translator.translate(word);

  console.log(result);
}, 500);

main();