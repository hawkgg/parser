<?php

class Parser
{
    private $cur;
    private $str;

    public function app($str)
    {
        return new self($str);
    }

    private function __construct($str)
    {
        $this->str = $str;
        $this->cur = 0;
    }

    public function moveto($pattern)
    {
        $res = strpos($this->str, $pattern, $this->cur);

        if ($res === false) {
            return -1;
        }

        return $this->cur = $res;
    }

    public function moveafter($pattern)
    {

        $res = strpos($this->str, $pattern, $this->cur);

        if ($res === false) {
            return -1;
        }

        return $this->cur = $res + strlen($pattern);
    }

    public function readto($pattern)
    {
        $res = strpos($this->str, $pattern, $this->cur);

        if ($res === false) {
            return -1;
        }

        $out = substr($this->str, $this->cur, $res - $this->cur);

        $this->cur = $res;

        return $out;
    }

    public function subtag($start_pattern, $tag)
    {
        $start = $this->moveafter($start_pattern);

        if ($start === -1) {
            return -1;
        }

        $open = '<' . $tag;
        $close = '</' . $tag . '>';

        $run = 1;

        while ($run) {
            $o = strpos($this->str, $open, $this->cur);
            $c = strpos($this->str, $close, $this->cur);

            if ($o === false || ($c < $o)) {
                $this->cur = $c + strlen($close);
                $run--;
            } else {
                $this->cur = $o + strlen($open);
                $run++;
            }
        }

        $start_cutting = $start - strlen($start_pattern);

        return substr($this->str, $start_cutting, $this->cur - $start_cutting);
    }
}