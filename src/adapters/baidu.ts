import { Adapter, Result } from './adapter'


class Baidu implements Adapter {

  key: string

  secret: string

  word: string = ''

  isChinese: boolean = false

  constructor(key: string, secret: string) {
    this.key = key
    this.secret = secret
  }

  url(word: string): string {
    this.isChinese = this.detectChinese(word)
    return 'http://openapi.youdao.com/api'
  }

  parse (response: any): Result[] {
    return []
  }
  
  private detectChinese(word: string): boolean {
    return /^[\u4e00-\u9fa5]+$/.test(word)
  }

}

export default Baidu