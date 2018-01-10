<?php
/**
 * This file is part of the ParsedownCheckbox package.
 *
 * (c) Simon Leblanc <contact@leblanc-simon.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ParsedownCheckbox extends ParsedownExtra
{
    const VERSION = '0.0.2';

    protected function blockListComplete($block)
    {
        if (null === $block) {
            return null;
        }

        if (
            false === isset($block['element'])
            || false === isset($block['element']['text'])
            || false === is_array($block['element']['text'])
        ) {
            return $block;
        }

        $count_element = count($block['element']['text']);
        for ($iterator_element = 0; $iterator_element < $count_element; $iterator_element++) {
            if (
                false === isset($block['element']['text'][$iterator_element]['text'])
                || false === is_array($block['element']['text'][$iterator_element]['text'])
            ) {
                continue;
            }

            $count_text = count($block['element']['text'][$iterator_element]['text']);
            for ($iterator_text = 0; $iterator_text < $count_text; $iterator_text++) {
                $begin_line = substr(trim($block['element']['text'][$iterator_element]['text'][$iterator_text]), 0, 4);
                if ('[ ] ' === $begin_line) {
                    $block['element']['text'][$iterator_element]['text'][$iterator_text] = '<input type="checkbox" disabled /> '.
                        substr(trim($block['element']['text'][$iterator_element]['text'][$iterator_text]), 4);
                    $block['element']['text'][$iterator_element]['attributes'] = ['class' => 'parsedown-task-list parsedown-task-list-open'];
                } elseif ('[x] ' === $begin_line) {
                    $block['element']['text'][$iterator_element]['text'][$iterator_text] = '<input type="checkbox" checked disabled /> '.
                        substr(trim($block['element']['text'][$iterator_element]['text'][$iterator_text]), 4);
                    $block['element']['text'][$iterator_element]['attributes'] = ['class' => 'parsedown-task-list parsedown-task-list-close'];
                }
            }
        }

        return $block;
    }
}

