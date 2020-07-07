<?php

namespace WebArch\BitrixUserPropertyType\Utils;

use HtmlObject\Element;
use HtmlObject\Input;

class HtmlHelper
{
    /**
     * @param string $name
     * @param bool $checked
     * @param string|null $id
     * @param string $value
     *
     * @return string
     */
    public function getInputTypeCheckbox(string $name, bool $checked, ?string $id = null, string $value = '1'): string
    {
        $attributes = [];
        if (!is_null($id)) {
            $attributes['id'] = $id;
        }
        if ($checked) {
            $attributes['checked'] = 'checked';
        }

        return Input::create('checkbox', $name, $value, $attributes);
    }

    /**
     * @param string $name
     * @param string $optionListHtml
     * @param string|null $id
     *
     * @param bool $required
     *
     * @return string
     */
    public function getSelect(string $name, string $optionListHtml, ?string $id = null, bool $required = false): string
    {
        $attributes = ['name' => $name];
        if (!is_null($id)) {
            $attributes['id'] = $id;
        }
        if ($required) {
            $attributes['required'] = 'required';
        }

        return Element::create('select', $optionListHtml, $attributes);
    }

    /**
     * @param string $title
     * @param string $optionListHtml
     *
     * @return string
     */
    public function addOptionTitle(string $title, string $optionListHtml): string
    {
        return $this->getOption($title, '', false, true) . $optionListHtml;
    }

    /**
     * @param int $from
     * @param int $until
     * @param string|null $selectedValue
     * @param string|null $disabledValue
     * @param int $step
     * @param string $format
     * @param bool $appendEmpty
     *
     * @return string
     */
    public function getOptionListWithNumbers(
        int $from,
        int $until,
        ?string $selectedValue = null,
        ?string $disabledValue = null,
        int $step = 1,
        string $format = '%d',
        bool $appendEmpty = true
    ): string {
        $optionList = [];
        if ($appendEmpty) {
            $optionList[] = [
                'text'  => 'пусто',
                'value' => '',
            ];
        }
        for ($i = $from; $i <= $until; $i += $step) {
            $optionList[] = ['text' => sprintf($format, $i)];
        }

        return $this->getOptionList($optionList, $selectedValue, $disabledValue);
    }

    /**
     * @param array<int, array<string>> $optionList Вида [['text' => 'foo', 'value' => 'bar'],...] или
     *  [['text' => 'foo'],...]
     * @param string $selectedValue
     * @param string $disabledValue
     *
     * @return string
     */
    public function getOptionList(
        array $optionList,
        ?string $selectedValue = null,
        ?string $disabledValue = null
    ): string {
        $html = '';
        foreach ($optionList as $option) {
            if (!is_array($option) || !array_key_exists('text', $option)) {
                continue;
            }
            $text = trim($option['text']);
            if (array_key_exists('value', $option)) {
                $value = trim($option['value']);
            } else {
                $value = $text;
            }
            $html .= ($this->getOption(
                $text,
                $value,
                $selectedValue === $value,
                $disabledValue === $value
            ));
        }

        return $html;
    }

    /**
     * @param string $text
     * @param string $value
     * @param bool $selected
     *
     * @param bool $disabled
     *
     * @return string
     */
    public function getOption(string $text, string $value, ?bool $selected = null, bool $disabled = false): string
    {
        $attributes = ['value' => htmlentities($value)];
        if ($disabled) {
            $attributes['disabled'] = 'disabled';
        }
        if (true === $selected) {
            $attributes['selected'] = 'selected';
        }

        return Element::create('option', htmlentities($text), $attributes);
    }
}
