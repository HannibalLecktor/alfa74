<?php namespace Xpage\SeoFilter;

class ValueModifier implements ValueModifierInterface
{
    public function modify($value) {
        return \CUtil::translit($value, 'ru',
            [
                "max_len"               => 100,
                "change_case"           => 'L',
                "replace_space"         => '_',
                "replace_other"         => '_',
                "delete_repeat_replace" => true,
                "safe_chars"            => '',
            ]
        );
    }
}