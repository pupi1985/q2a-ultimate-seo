<?php

function qa_q_request($questionid, $title)
{
    // For qa_string_to_words(), qa_block_words_to_preg(), qa_block_words_replace(), qa_strlen()
    require_once QA_INCLUDE_DIR . 'util/string.php';

    // URL Clean
    if (qa_opt('useo_url_cleanup')) { //clean url's title
        $words = qa_opt('useo_url_words_list');
        $word_list = qa_block_words_to_preg($words);
        $newTitle = trim(qa_block_words_replace($title, $word_list, ''));

        if (qa_strlen($newTitle) > 0 || !qa_opt('useo_url_dont_make_empty')) {
            $title = $newTitle;
        }
    }

    // URL Customization
    $type = (int)qa_opt('useo_url_q_uppercase_type');
    $url = qa_q_request_base($questionid, $title);
    if ($type === 0) { // early return, if possible
        return $url;
    }

    $parts = explode('/', $url);
    $parts[1] = useo_capitalize($type, $parts[1]);

    return implode('/', $parts);
}

function qa_tag_html($tag, $microdata = false, $favorited = false)
{
    // For qa_string_to_words(), qa_strtolower()
    require_once QA_INCLUDE_DIR . 'util/string.php';

    // URL Customization
    $type = (int)qa_opt('useo_url_tag_uppercase_type');
    $taglink = useo_capitalize($type, $tag);

    // Tag Description
    global $useo_tag_desc_list;

    $taglc = qa_strtolower($tag);
    $useo_tag_desc_list[$taglc] = true;

    $url = qa_path_html('tag/' . $taglink);
    $attrs = $microdata ? ' rel="tag"' : '';
    $class = $favorited ? ' qa-tag-favorited' : '';

    return '<a href="' . $url . '"' . $attrs . ' class="qa-tag-link' . $class . '">' . qa_html($tag) . '</a>';
}

function qa_sanitize_html($html, $linksnewwindow = false, $storage = false)
{
    $safe = qa_sanitize_html_base($html, $linksnewwindow, $storage);

    if ($safe === '') {
        return '';
    }

    return updateRelAttributeFromHtml($safe);
}

function qa_html_convert_urls($html, $newwindow = false)
{
    $html = qa_html_convert_urls_base($html, $newwindow);

    return updateRelAttributeFromHtml($html);
}
