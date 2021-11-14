interface IItem {
  title: string;
  subtitle: string;
  valid: boolean;
  uid?: string;
  arg?: string;
  autocomplete?: string;
  type?: string;
  quicklookurl?: string;
  icon?: any;
  mods?: any;
  text?: string;
}

class Item {
  private uid?: string;

  private arg?: string;

  private valid: boolean = true;

  private autocomplete?: string;

  private title?: string;

  private subtitle?: string;

  private icon?: any;

  private type?: string;

  private text: any;

  private quicklookurl: any[] = [];

  private mods: any = {};

  setTitle(title: string): this {
    this.title = title;
    return this;
  }

  setSubtitle(subtitle: string): this {
    this.subtitle = subtitle;
    return this;
  }

  setIcon(path: string, type?: string): this {
    this.icon = { path };

    if (["fileicon", "filetype"].some((t) => t === type)) {
      this.icon.type = type;
    }

    return this;
  }

  setFileiconIcon(path: string): this {
    this.setIcon(path, "fileicon");
    return this;
  }

  setFiletypeIcon(path: string): this {
    this.setIcon(path, "filetype");
    return this;
  }

  setValid(valid: boolean = true): this {
    this.valid = valid;
    return this;
  }

  setType(type: any, verify_existence: boolean = true): this {
    if (["default", "file", "file:skipcheck"].some((t) => t === type)) {
      if (type === "file" && verify_existence === false) {
        type === "file:skipcheck";
      }
      this.type = type;
    }
    return this;
  }

  setArg(arg: string): this {
    this.arg = arg;
    return this;
  }

  setText(type: string, text: string): this {
    if (!["copy", "largetype"].some((t) => t === type)) {
      return this;
    }
    this.text = { [type]: text };
    return this;
  }

  setCopy(text: string): this {
    return this.setText("copy", text);
  }

  setLargetype(text: string): this {
    return this.setText("largetype", text);
  }

  setMod(mod: any, subtitle: string, arg: string, valid: boolean = true): this {
    if (!["shift", "fn", "ctrl", "alt", "cmd"].some((t) => t === mod)) {
      return this;
    }
    this.mods[mod] =  { subtitle, arg, valid };
    return this;
  }

  setCmd(subtitle: string, arg: string, valid: boolean = true): this {
    return this.setMod("cmd", subtitle, arg, valid);
  }

  setShift(subtitle: string, arg: string, valid: boolean = true): this {
    return this.setMod("shift", subtitle, arg, valid);
  }

  setFn(subtitle: string, arg: string, valid: boolean = true): this {
    return this.setMod("fn", subtitle, arg, valid);
  }

  setCtrl(subtitle: string, arg: string, valid: boolean = true): this {
    return this.setMod("ctrl", subtitle, arg, valid);
  }

  setAlt(subtitle: string, arg: string, valid: boolean = true): this {
    return this.setMod("alt", subtitle, arg, valid);
  }

  result(): any {
    const attrs = [
      "uid",
      "arg",
      "autocomplete",
      "title",
      "subtitle",
      "type",
      "valid",
      "quicklookurl",
      "icon",
      "mods",
      "text",
    ];

    const result = {};

    attrs.forEach((attr) => {
      //这里的判断是非空， 如果是数组，数组长度为不能 0
      if (this[attr]) {
        if (Array.isArray(this[attr]) && this[attr].length === 0) {
          // do nothing.
        } else {
          result[attr] = this[attr];
        }
      }
    });

    return result as IItem;
  }
}

export default Item;
