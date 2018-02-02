<?php

class Parser
{
    private $cur; // позиция курсора (int)
    public $str; // строка для парсинга

    public function app($str)
    {
        return new self($str);
    }

    private function __construct($str)
    {
        $this->str = $str;
        $this->cur = 0;
    }

    /**
     * Ставит курсор до шаблона
     * 
     * @param string $start_pattern
     * Шаблон для поиска
     */
    public function moveto($pattern)
    {
        $res = strpos($this->str, $pattern, $this->cur);

        if ($res === false) {
            return -1;
        }

        return $this->cur = $res;
    }

    /**
     * Ставит курсор после шаблона
     * 
     * @param string $start_pattern
     * Шаблон для поиска
     */
    public function moveafter($pattern)
    {

        $res = strpos($this->str, $pattern, $this->cur);

        if ($res === false) {
            return -1;
        }

        return $this->cur = $res + strlen($pattern);
    }

    /**
     * Читает справа от курсора
     * 
     * @param string $start_pattern
     * Шаблон для поиска
     * 
     * @param bool $need_move
     * Флаг передвижения курсора
     */
    public function readto($pattern = false, $need_move = true)
    {
        if (!$pattern) {
            $res = strlen($this->str);
        } else {
            $res = strpos($this->str, $pattern, $this->cur);
        }

        if ($res === false) {
            return -1;
        }

        $out = substr($this->str, $this->cur, $res - $this->cur);
        
        if ($need_move) {
            $this->cur = $res;
        }

        return $out;
    }

    /**
     * Удаляет комменты
     */
    public function delete_comments()
    {
        while (strpos($this->str, '<!--') !== false && strpos($this->str, '-->') !== false) {
            $str .= $this->readto('<!--');
            $this->moveafter('-->');
            $this->str = $this->readto(false, false);
            $this->cur_reset();
        }

        $this->str = $str . $this->str;

        if (strpos($this->str, '<!--') !== false) {
            $this->str = $this->readto('<!--');
            $this->cur_reset();
        }

        if (strpos($this->str, '-->') !== false) {
            $this->moveafter('-->');
            $this->str = $this->readto();
            $this->cur_reset();
        }

        return $this->str;
    }

    /**
     * Обнуляет указатель
     */
    public function cur_reset()
    {
        return $this->cur = 0;
    }    
            
    /**
     * Читает слева от курсора
     * 
     * @param string $start_pattern
     * Шаблон для поиска
     * 
     * @param bool $need_move
     * Флаг передвижения курсора
     */
    public function readfrom($pattern, $need_move = false)
    {
        $res = strpos($this->str, $pattern);

        if ($res === false || $res >= $this->cur) {
            return -1;
        }

        $out = substr($this->str, $res, $this->cur - $res);

        if ($need_move) {
            $this->cur = $res;
        }

        return $out;
    }

    /**
     * Вырезает выбранный тег
     * 
     * @param string $start_pattern
     * Шаблон для поиска
     * 
     * @param string $tag
     * Название тега
     */
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