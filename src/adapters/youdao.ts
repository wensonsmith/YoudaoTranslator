import { Adapter, Result } from "./adapter";
import md5 from "../libs/md5";

class Youdao implements Adapter {
  key: string;

  secret: string;

  word: string = "";

  isChinese: boolean = false;

  results: Result[] = [];

  phonetic: string = "";

  constructor(key: string, secret: string) {
    this.key = key;
    this.secret = secret;
  }

  url(word: string): string {
    this.isChinese = this.detectChinese(word);
    this.word = word;

    const from = this.isChinese ? "zh-CHS" : "auto";
    const to = this.isChinese ? "en" : "zh-CHS";
    const salt = Math.floor(Math.random() * 10000).toString();
    const sign = md5(`${this.key}${word}${salt}${this.secret}`);

    const params = new URLSearchParams({
      q: word,
      from,
      to,
      appKey: this.key,
      salt,
      sign,
    });

    return "https://openapi.youdao.com/api?" + params.toString();
  }

  parse(data: any): Result[] {
    if (data.errorCode !== "0") {
      return this.parseError(data.errorCode);
    }

    const { translation, basic, web } = data;

    this.parseTranslation(translation);
    this.parseBasic(basic);
    this.parseWeb(web);

    return this.results;
  }

  private parseTranslation(translation: object) {
    if (translation) {
      const pronounce = this.isChinese ? translation[0] : this.word;
      this.addResult( translation[0], this.word, translation[0], pronounce );
    }
  }

  private parseBasic(basic: any) {
    if (basic) {
      let pronounce;
      basic.explains.forEach((explain) => {
        pronounce = this.isChinese ? explain : this.word;
        this.addResult(explain, this.word, explain, pronounce);
      });

      if (basic.phonetic) {
        // 获取音标，同时确定要发音的单词
        const phonetic: string = this.parsePhonetic(basic);
        this.addResult( phonetic, "回车可听发音", "~" + pronounce, pronounce );
      }
    }
  }

  private parseWeb(web: any) {
    if (web) {
      web.forEach((item, index) => {
        let pronounce = this.isChinese ? item.value[0] : item.key;
        this.addResult( item.value.join(", "), item.key, item.value[0], pronounce);
      });
    }
  }

  private parsePhonetic(basic: any): string {
    let phonetic: string = '';

    if (this.isChinese && basic.phonetic) {
      phonetic = "[" + basic.phonetic + "] ";
    }

    if (basic["us-phonetic"]) {
      phonetic += " [美: " + basic["us-phonetic"] + "] ";
    }

    if (basic["uk-phonetic"]) {
      phonetic += " [英: " + basic["uk-phonetic"] + "]";
    }

    return phonetic;
  }

  private parseError(code: number): Result[] {
    const messages = {
      101: "缺少必填的参数",
      102: "不支持的语言类型",
      103: "翻译文本过长",
      108: "应用ID无效",
      110: "无相关服务的有效实例",
      111: "开发者账号无效",
      112: "请求服务无效",
      113: "查询为空",
      202: "签名检验失败,检查 KEY 和 SECRET",
      401: "账户已经欠费",
      411: "访问频率受限",
    };

    const message = messages[code] || "请参考错误码：" + code;

    return this.addResult("👻 翻译出错啦", message, "Ooops...");
  }

  private addResult( title: string, subtitle: string, arg: string = "", pronounce: string = ""): Result[] {
    const quicklookurl = "https://www.youdao.com/w/" + this.word;

    const maxLength = this.detectChinese(title) ? 27 : 60;
    
    if (title.length > maxLength) {
      const copy = title;
      title = copy.slice(0, maxLength);
      subtitle = copy.slice(maxLength);
    }

    this.results.push({ title, subtitle, arg, pronounce, quicklookurl });
    return this.results;
  }

  private detectChinese(word: string): boolean {
    return /^[\u4e00-\u9fa5]+$/.test(word);
  }
}

export default Youdao;
