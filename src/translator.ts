import { Adapter } from "./adapters/adapter";
import Adapters from "./adapters";
import redaxios from "./libs/redaxios";
import Workflow from "./workflow/workflow";

interface ITranslator {
  adapter: Adapter;
  translate: (word: string) => Promise<any>;
}

class Translator implements ITranslator {
  adapter: Adapter;

  constructor(key: string, secret: string, platform: string) {
    this.adapter = new Adapters[platform](key, secret);
  }

  public async translate(query: string): Promise<any> {
    // camel case to space case
    const word = query.replace(/([A-Z])/g, " $1").toLowerCase();
    // url
    const url = this.adapter.url(word);
    // fetch
    const response = await redaxios.create().get(url);
    // parse
    let result = this.adapter.parse(response.data);
    // compose
    const isChinese = this.detectChinese(word);
    if (isChinese) {
      result = await this.trimResultPhonetic(result);
    }

    return new Workflow().compose(result).output();
  }

  private async trimResultPhonetic(originalResult: any[]): Promise<any[]> {
    const newResult = JSON.parse(JSON.stringify(originalResult));
    const phoneticItemIndex = this.getPhoneticItemIndex(newResult);
    for (let i = 0; i < phoneticItemIndex; i++) {
      const response = await redaxios
        .create()
        .get(this.adapter.url(newResult[i].arg));
      const phonetic = response.data.basic["us-phonetic"];
      newResult[i].title = `${newResult[i].title} [${phonetic}]`;
    }
    return newResult;
  }

  private getPhoneticItemIndex(array: any[]): number {
    return array.findIndex((item) => item.arg[0] === "~");
  }

  private detectChinese(word: string): boolean {
    return /^[\u4e00-\u9fa5]+$/.test(word);
  }
}

export default Translator;
