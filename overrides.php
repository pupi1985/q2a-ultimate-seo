<?php
if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
    header('Location: ../');
    exit;
}

function qa_q_request($questionid, $title)
{
    // URL Clean
    if (qa_opt('useo_url_cleanup')) { //clean url's title
        $words = qa_opt('useo_url_words_list');
        $raw_title = $title;
        // ~~ str_replace method
        //$word_list = explode(',', $words);
        //$title = str_replace($word_list, '', $raw_title);

        // ~~preg_replace method with Q2A functions
        require_once QA_INCLUDE_DIR . 'util/string.php';
        $word_list = qa_block_words_to_preg($words);
        $title = trim(qa_block_words_replace($title, $word_list, ''));

        if ((strlen($title) == 0) && (qa_opt('useo_url_dont_make_empty'))) {
            $title = $raw_title;
        }
    }

    $url = qa_q_request_base($questionid, $title);

    // capitalize letters
    if (qa_opt('useo_url_q_uppercase')) {
        $type = qa_opt('useo_url_q_uppercase_type');
        if ($type == 1) { // first word's first letter
            $parts = explode('/', $url);
            $parts[1] = ucfirst($parts[1]);
            $url = implode('/', $parts);
        } else if ($type == 2) // all word's first letter
        {
            $url = str_replace(' ', '?', ucwords(str_replace('?', ' ', str_replace(' ', '/', ucwords(str_replace('/', ' ', str_replace(' ', '-', ucwords(str_replace('-', ' ', strtolower($url))))))))));
        } else // whole words
        {
            $url = strtoupper($url);
        }
    }

    return $url;
}

function qa_tag_html($tag, $microformats = false, $favorited = false)
{
    // URL Customization
    if(qa_opt('useo_url_tag_uppercase')) {
      $type = qa_opt('useo_url_tag_uppercase_type');
      if ($type == 1) { // first word's first letter
         $taglink = ucfirst($tag);
      } else if ($type == 2) // all word's first letter
      {
        $taglink = str_replace(' ', '?', ucwords(str_replace('?', ' ', str_replace(' ', '/', ucwords(str_replace('/', ' ', str_replace(' ', '-', ucwords(str_replace('-', ' ', strtolower($tag))))))))));
      } else // whole words
      {
         $taglink = strtoupper($tag);
      }
    }
    else $taglink = $tag;
    // Tag Description
    global $useo_tag_desc_list;
    require_once QA_INCLUDE_DIR . 'util/string.php';

    $taglc = qa_strtolower($tag);
    $useo_tag_desc_list[$taglc] = true;

    return '<a href="' . qa_path_html('tag/' . $taglink) . '"' . ($microformats ? ' rel="tag"' : '') . ' class="qa-tag-link' .
        ($favorited ? ' qa-tag-favorited' : '') . '">' . qa_html($tag) . '</a>';
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
