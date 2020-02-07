
YoudaoTranslate | 有道翻译
===============

![screenshot_1](screenshot/screenshot_1.png)

## 一、 特性

#### 1. 中英文自动互翻

#### 2. 快捷键支持
- 双击 `⌥ Alt`  可以翻译选中内容 (在 Hotkey 中设置)

- `⇧ Shift` + `Space` 可以直接唤出翻译输入框(在 Hotkey 中设置)

#### 3. 英文发音
-  `⌘ Command` + `↩︎ Enter` 本地发音（系统 `Say` 命令）
- `⌥ Alt` + `↩︎ Enter`  调用有道在线语音发音
- 在音标上回车发音并关闭窗口

![screenshot_3](screenshot/screenshot_3.png)

#### 4. 在选项上 `↩︎ Enter` 回车复制翻译结果

#### 5. 输入 `yd *` 查询最近的翻译记录

![transalte_history](screenshot/translate_history.gif)

#### 6. 翻译结果上按 `⇧ Shift` 直接预览有道网页

![screenshot_4](screenshot/screenshot_4.gif)

#### 7. 输入 `update` 检查更新 Workflow

![updae](screenshot/update.png)

## 二、开始使用

#### 1. 下载导入使用
- [GitHub Releases 下载](https://github.com/wensonsmith/YoudaoTranslate/releases)
- [又拍云下载 v2.4.0](https://img.seekbetter.me/workflows/Youdao%20Translate.zip)

   下载解压后双击 `Youdao Translate.alfredworkflow` 导入即可使用。

#### 2. 使用源码

```shell
$ git clone https://github.com/wensonsmith/YoudaoTranslate.git /path/to/YoudaoTranslate
// 请注意这里是 Alfred 3 的路径
$ ln -s /path/to/YoudaoTranslate/src/ ~/Library/Application\ Support/Alfred\ 3/Alfred.alfredpreferences/workflows/YoudaoTranslate
```
以后只需要进入 `/path/to/YoudaoTranslate` 进行 `git pull` 即可更新

## 三、 配置有道翻译

> 2017年9月3日更新
> 由于有道翻译 api 迁移到了有道智云，所以大家需要去[有道智云](http://ai.youdao.com/)进行注册然后创建应用了

1. 注册[有道智云](https://ai.youdao.com/)帐号

2. 创建一个[自然语言翻译服务](https://ai.youdao.com/fanyi-services.s)

3. 创建一个[有道云应用](https://ai.youdao.com/appmgr.s)并绑定第二步创建的服务

4. 这样就可以获得应用(appKey)和密钥(secret)了

   [应用](https://ai.youdao.com/appmgr.s) - 应用详情 - 应用ID appKey, 应用密钥 secret

5. 把变量填入 workflow 右上角的 [X] 点开后的配置框中


![var-config-step-1](screenshot/var-config-step-1.jpg)

![var-config-step-2](screenshot/var-config-step-2.png)


## 四、Contributors

<a href="https://iwenson.com" target="_blank"><img src="https://avatars0.githubusercontent.com/u/2544185?s=60&v=4" /></a> 
<a href="https://blog.zthxxx.me" target="_blank"><img src="https://avatars3.githubusercontent.com/u/15135943?s=60&v=4" /></a> 

## 五、 使用库和参考资料

- https://github.com/joetannenbaum/alfred-workflow
- https://www.alfredapp.com/help/workflows/inputs/script-filter/json/
- https://www.alfredapp.com/help/workflows/
