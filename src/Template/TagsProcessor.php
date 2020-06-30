<?php

abstract class TagsProcessor
{
    /**
     * @param string $text
     * @param array $data
     * @return string
     */
    abstract public function process($text, array $data = []);

    /**
     * @param string $name
     * @param string $text
     * @return bool
     */
    protected function hasTag($name, $text)
    {
        return strpos($text, sprintf('[%s]', $name)) !== false;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @param string $text
     * @return string
     */
    protected function replaceTag($name, $value, $text)
    {
        return str_replace(sprintf('[%s]', $name), $value, $text);
    }
}
