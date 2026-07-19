import { getEnv, getArgs } from "./runtime";
import Translator from "./translator";

const main = async () => {
  const translator = new Translator(
    getEnv("key"),
    getEnv("secret"),
    getEnv("platform")
  );

  const word: string = getArgs().pop() as string;

  const result = await translator.translate(word);

  console.log(result);
};

main();
