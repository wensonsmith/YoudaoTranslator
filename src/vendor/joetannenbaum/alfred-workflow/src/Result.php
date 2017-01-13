<?php

namespace Alfred\Workflows;

use SimpleXMLElement;

class Result
{
    protected $uid;

    protected $arg;

    protected $valid = true;

    protected $autocomplete;

    protected $title;

    protected $subtitle;

    protected $icon;

    protected $type;

    protected $text = [];

    protected $quicklookurl;

    protected $mods = [];

    /**
     * @param bool $valid
     *
     * @return \Alfred\Workflows\Result
     */
    protected function setValid($valid = true)
    {
        $this->valid = !!$valid;

        return $this;
    }

    /**
     * @param string $type (deafult|file|file:skipcheck)
     * @param bool $verify_existence When used with $type 'file'
     *
     * @return \Alfred\Workflows\Result
     */
    protected function setType($type, $verify_existence = true)
    {
        if (in_array($type, ['default', 'file', 'file:skipcheck'])) {
            if ($type === 'file' && $verify_existence === false) {
                $type = 'file:skipcheck';
            }

            $this->type = $type;
        }

        return $this;
    }

    /**
     * @param string $path
     * @param string|null $type (fileicon|filetype)
     *
     * @return \Alfred\Workflows\Result
     */
    protected function setIcon($path, $type = null)
    {
        $this->icon = [
            'path' => $path,
        ];

        if (in_array($type, ['fileicon', 'filetype'])) {
            $this->icon['type'] = $type;
        }

        return $this;
    }

    /**
     * @param string $path
     *
     * @return \Alfred\Workflows\Result
     */
    protected function setFileiconIcon($path)
    {
        return $this->setIcon($path, 'fileicon');
    }

    /**
     * @param string $path
     *
     * @return \Alfred\Workflows\Result
     */
    protected function setFiletypeIcon($path)
    {
        return $this->setIcon($path, 'filetype');
    }

    /**
     * @param string $subtitle
     *
     * @return \Alfred\Workflows\Result
     */
    protected function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    /**
     * @param string $type (copy|largetype)
     * @param string $text
     *
     * @return \Alfred\Workflows\Result
     */
    protected function setText($type, $text)
    {
        if (!in_array($type, ['copy', 'largetype'])) {
            return $this;
        }

        $this->text[$type] = $text;

        return $this;
    }

    /**
     * @param string $copy
     *
     * @return \Alfred\Workflows\Result
     */
    protected function setCopy($copy)
    {
        return $this->setText('copy', $copy);
    }

    /**
     * @param string $largetype
     *
     * @return \Alfred\Workflows\Result
     */
    protected function setLargetype($largetype)
    {
        return $this->setText('largetype', $largetype);
    }

    /**
     * @param string $mod (shift|fn|ctrl|alt|cmd)
     * @param string $subtitle
     * @param string $arg
     * @param bool $valid
     *
     * @return \Alfred\Workflows\Result
     */
    protected function setMod($mod, $subtitle, $arg, $valid = true)
    {
        if (!in_array($mod, ['shift', 'fn', 'ctrl', 'alt', 'cmd'])) {
            return $this;
        }

        $this->mods[$mod] = compact('subtitle', 'arg', 'valid');

        return $this;
    }

    /**
     * @param string $subtitle
     * @param string $arg
     * @param bool $valid
     *
     * @return \Alfred\Workflows\Result
     */
    protected function setCmd($subtitle, $arg, $valid = true)
    {
        return $this->setMod('cmd', $subtitle, $arg, $valid);
    }

    /**
     * @param string $subtitle
     * @param string $arg
     * @param bool $valid
     *
     * @return \Alfred\Workflows\Result
     */
    protected function setShift($subtitle, $arg, $valid = true)
    {
        return $this->setMod('shift', $subtitle, $arg, $valid);
    }

    /**
     * @param string $subtitle
     * @param string $arg
     * @param bool $valid
     *
     * @return \Alfred\Workflows\Result
     */
    protected function setFn($subtitle, $arg, $valid = true)
    {
        return $this->setMod('fn', $subtitle, $arg, $valid);
    }

    /**
     * @param string $subtitle
     * @param string $arg
     * @param bool $valid
     *
     * @return \Alfred\Workflows\Result
     */
    protected function setCtrl($subtitle, $arg, $valid = true)
    {
        return $this->setMod('ctrl', $subtitle, $arg, $valid);
    }

    /**
     * @param string $subtitle
     * @param string $arg
     * @param bool $valid
     *
     * @return \Alfred\Workflows\Result
     */
    protected function setAlt($subtitle, $arg, $valid = true)
    {
        return $this->setMod('alt', $subtitle, $arg, $valid);
    }

    /**
     * Converts the results to an array structured for Alfred
     *
     * @return array
     */
    public function toArray()
    {
        $attrs = [
            'uid',
            'arg',
            'autocomplete',
            'title',
            'subtitle',
            'type',
            'valid',
            'quicklookurl',
            'icon',
            'mods',
            'text',
        ];

        $result = [];

        foreach ($attrs as $attr) {
            if (is_array($this->$attr)) {
                if (count($this->$attr) > 0) {
                    $result[$attr] = $this->$attr;
                }
                continue;
            }

            if ($this->$attr !== null) {
                $result[$attr] = $this->$attr;
            }
        }

        ksort($result);

        return $result;
    }

    public function __get($property)
    {
        return $this->$property;
    }

    public function __call($method, $args)
    {
        $setter = 'set' . ucwords($method);

        if (method_exists($this, $setter)) {
            call_user_func_array([$this, $setter], $args);

            return $this;
        }

        if (property_exists($this, $method)) {
            $this->$method = reset($args);

            return $this;
        }
    }
}
