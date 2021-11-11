<?php

class useo_options
{
    private $directory;
    private $urltoroot;
    private $saved;

    function load_module($directory, $urltoroot)
    {
        $this->directory = $directory;
        $this->urltoroot = $urltoroot;
    }

    public function option_default($option)
    {
        switch ($option) {
            case 'useo_link_relations':
                return '[]';
        }

        return null;
    }

    function match_request($request)
    {
        return $request === 'admin/ultimate_seo';
    }

    function process_request($request)
    {
        // For qa_admin_check_privileges()
        require_once QA_INCLUDE_DIR . 'app/admin.php';

        $qa_content = qa_content_prepare();

        if (!qa_admin_check_privileges($qa_content)) {
            return $qa_content;
        }

        $qa_content['site_title'] = 'Q2A Ultimate SEO by QA-Themes.com';
        $qa_content['title'] = 'Ultimate SEO';

        if (!isset($qa_content['body_footer'])) {
            $qa_content['body_footer'] = '';
        }

        $qa_content['body_footer'] .= sprintf('<script src="%s"></script>', $this->urltoroot . 'include/easy-responsive-tabs/js/easyResponsiveTabs.js');
        $qa_content['body_footer'] .= sprintf('<script src="%s"></script>', $this->urltoroot . 'include/main.js');

        if (!isset($qa_content['css_src'])) {
            $qa_content['css_src'] = array();
        }

        $qa_content['css_src'][] = $this->urltoroot . 'include/easy-responsive-tabs/css/easy-responsive-tabs.min.css';
        $qa_content['css_src'][] = $this->urltoroot . 'include/style.css';

        $qa_content['custom'] = $this->page_form();
        //empty sidebar's content
        $qa_content['sidepanel'] = '';
        $qa_content['sidebar'] = '';
        unset($qa_content['widgets']);
        $qa_content['widgets'] = array();

        return $qa_content;
    }

    function page_form()
    {
        $output = '';
        if ((qa_clicked('useo_save')) && ($this->saved == false)) {
            // Title section
            qa_opt('useo_title_qa', qa_post_text('useo_title_qa'));
            qa_opt('useo_title_qa_item', qa_post_text('useo_title_qa_item'));
            qa_opt('useo_title_recent', qa_post_text('useo_title_recent'));
            qa_opt('useo_title_hot', qa_post_text('useo_title_hot'));
            qa_opt('useo_title_voted', qa_post_text('useo_title_voted'));
            qa_opt('useo_title_answered', qa_post_text('useo_title_answered'));
            qa_opt('useo_title_viewed', qa_post_text('useo_title_viewed'));
            qa_opt('useo_title_question', qa_post_text('useo_title_question'));
            qa_opt('useo_title_questions', qa_post_text('useo_title_questions'));
            qa_opt('useo_title_activity', qa_post_text('useo_title_activity'));
            qa_opt('useo_title_ask', qa_post_text('useo_title_ask'));
            qa_opt('useo_title_activity', qa_post_text('useo_title_activity'));
            qa_opt('useo_title_categories', qa_post_text('useo_title_categories'));
            qa_opt('useo_title_category', qa_post_text('useo_title_category'));
            qa_opt('useo_title_tags', qa_post_text('useo_title_tags'));
            qa_opt('useo_title_tag', qa_post_text('useo_title_tag'));
            qa_opt('useo_title_unanswered', qa_post_text('useo_title_unanswered'));
            qa_opt('useo_title_unselected', qa_post_text('useo_title_unselected'));
            qa_opt('useo_title_unupvoted', qa_post_text('useo_title_unupvoted'));
            qa_opt('useo_title_search', qa_post_text('useo_title_search'));
            qa_opt('useo_title_users', qa_post_text('useo_title_users'));
            qa_opt('useo_title_user', qa_post_text('useo_title_user'));
            // URLs section
            qa_opt('useo_url_cleanup', (int)qa_post_text('useo_url_cleanup'));
            qa_opt('useo_url_dont_make_empty', (int)qa_post_text('useo_url_dont_make_empty'));
            qa_opt('useo_url_q_uppercase_type', (int)qa_post_text('useo_url_q_uppercase_type'));
            qa_opt('useo_url_tag_uppercase_type', (int)qa_post_text('useo_url_tag_uppercase_type'));
            $words = qa_post_text('useo_url_words_raw');
            qa_opt('useo_url_words_raw', $words);
            $words_list = implode(',', preg_split('/' . QA_PREG_BLOCK_WORD_SEPARATOR . '+/', $words, -1, PREG_SPLIT_NO_EMPTY));
            qa_opt('useo_url_words_list', $words_list);

            // SEO Links section
            $rel_options = array();
            if (isset($_POST['useo_link_url'])) {
                $link = $_POST['useo_link_url'];
                $rel = $_POST['useo_link_rel'];
                $count = 0;
                foreach ($link as $key => $value) {
                    $rel_options[$count]['url'] = $value;
                    $rel_options[$count]['rel'] = $rel[$key];
                    $count++;
                }
            }
            qa_opt('useo_link_relations', json_encode($rel_options));

            // XML sitemap section
            qa_opt('useo_sitemap_enable', (int)qa_post_text('useo_sitemap_enable'));
            qa_opt('useo_sitemap_question_count', (int)qa_post_text('useo_sitemap_question_count'));
            qa_opt('useo_sitemap_users_enable', (int)qa_post_text('useo_sitemap_users_enable'));
            qa_opt('useo_sitemap_users_count', (int)qa_post_text('useo_sitemap_users_count'));
            qa_opt('useo_sitemap_tags_enable', (int)qa_post_text('useo_sitemap_tags_enable'));
            qa_opt('useo_sitemap_tags_count', (int)qa_post_text('useo_sitemap_tags_count'));
            qa_opt('useo_sitemap_categories_enable', (int)qa_post_text('useo_sitemap_categories_enable'));
            qa_opt('useo_sitemap_categoriy_q_enable', (int)qa_post_text('useo_sitemap_categoriy_q_enable'));
            qa_opt('useo_sitemap_categoriy_q_count', (int)qa_post_text('useo_sitemap_categoriy_q_count'));
            // Accessibility section
            qa_opt('useo_access_noindex', (int)qa_post_text('useo_access_noindex'));
            qa_opt('useo_access_nofollow', (int)qa_post_text('useo_access_nofollow'));
            qa_opt('useo_access_length_enable', (int)qa_post_text('useo_access_length_enable'));
            qa_opt('useo_access_length', (int)qa_post_text('useo_access_length'));
            // Meta Tags
            qa_opt('useo_meta_desc_ans_enable', (int)qa_post_text('useo_meta_desc_ans_enable'));
            qa_opt('useo_meta_desc_sel_ans_enable', (int)qa_post_text('useo_meta_desc_sel_ans_enable'));
            qa_opt('useo_meta_desc_length', (int)qa_post_text('useo_meta_desc_length'));
            qa_opt('useo_meta_editor_enable', (int)qa_post_text('useo_meta_editor_enable'));
            qa_opt('useo_meta_home_title', qa_post_text('useo_meta_home_title'));
            qa_opt('home_description', qa_post_text('home_description')); // native option
            qa_opt('useo_meta_home_keywords', qa_post_text('useo_meta_home_keywords'));

            // Social Tags
            qa_opt('useo_social_og_enable_auto', (int)qa_post_text('useo_social_og_enable_auto'));
            qa_opt('useo_social_og_desc_length', qa_post_text('useo_social_og_desc_length'));
            qa_opt('useo_social_og_image', qa_post_text('useo_social_og_image'));
            qa_opt('useo_social_tc_enable', (int)qa_post_text('useo_social_tc_enable'));
            qa_opt('useo_social_tc_desc_length', qa_post_text('useo_social_tc_desc_length'));
            qa_opt('useo_social_tc_image', qa_post_text('useo_social_tc_image'));
            qa_opt('useo_social_tc_handler', qa_post_text('useo_social_tc_handler'));
            qa_opt('useo_social_enable_editor', (int)qa_post_text('useo_social_enable_editor'));
            // Tags
            qa_opt('useo_tag_desc_max_len', (int)qa_post_text('useo_tag_desc_max_len'));
            qa_opt('useo_tag_desc_enable_icon', (int)qa_post_text('useo_tag_desc_enable_icon'));
            qa_opt('useo_tag_desc_icon_height', (int)qa_post_text('useo_tag_desc_icon_height'));
            qa_opt('useo_tag_desc_icon_width', (int)qa_post_text('useo_tag_desc_icon_width'));
            qa_opt('useo_tag_desc_format', (int)qa_post_text('useo_tag_desc_format'));
            qa_opt('useo_tag_desc_permit_edit', (int)qa_post_text('useo_tag_desc_permit_edit'));
            // Categories
            qa_opt('useo_cat_title_qlist_enable', (int)qa_post_text('useo_cat_title_qlist_enable'));
            qa_opt('useo_cat_title_nav_enable', (int)qa_post_text('useo_cat_title_nav_enable'));
            qa_opt('useo_cat_desc_max_len', (int)qa_post_text('useo_cat_desc_max_len'));
            qa_opt('useo_cat_desc_format', (int)qa_post_text('useo_cat_desc_format'));
            qa_opt('useo_cat_desc_permit_edit', (int)qa_post_text('useo_cat_desc_permit_edit'));
            qa_opt('useo_cat_canonical_enable', (int)qa_post_text('useo_cat_canonical_enable'));
            // ~~~
            $output .= '<div class="qa-form-tall-ok">Settings were saved.</div>';
            $this->saved = true;
        } else {
            $this->saved = false;
        }
        if (qa_clicked('useo_reset')) {
            useo_reset_settings();
        }
        global $qa_modules;
        $output .= '
			<form name="useo" id="useo" action="' . qa_self_html() . '" method="post">
				<div id="verticalTab">
					<ul class="resp-tabs-list tab_identifier_child">
						<li class="tab_identifier_child">Title<span>Page Title Customizations</span></li>
						<li class="tab_identifier_child">URLs<span>URL Customizations</span></li>
						<li class="tab_identifier_child">Links<span>Link Optimizations</span></li>
						<li class="tab_identifier_child">Sitemap<span>Scalable XML Sitemap</span></li>
						<li class="tab_identifier_child">Accessibility<span>Accessibility for Search engines</span></li>
						<li class="tab_identifier_child">Meta Tags<span>Meta Tag Options</span></li>
						<li class="tab_identifier_child">Social Sharing<span>Social Media Meta Tags</span></li>
						<li class="tab_identifier_child">Tags<span>Optimizing Question Tags</span></li>
						<li class="tab_identifier_child">Categories<span>Optimizing Categories</span></li>
					</ul>
					<div class="resp-tabs-container tab_identifier_child">
						<div class="tab_identifier_child">
							' . $this->get_page_contents('title.php') . '
						</div>  
						<div class="tab_identifier_child">
							' . $this->get_page_contents('urls.php') . '
						</div>
						<div class="tab_identifier_child">
							' . ((isset($qa_modules['module']['link optimizer Admin'])) ? 'You have installed <strong>"SEO Links"</strong> plugin which is outdated and replaced by this plugin. please remove it to enable SEO features in this section.' : $this->get_page_contents('links.php')) . '
						</div>  
						<div class="tab_identifier_child">
							' . ((isset($qa_modules['module']['Scalable XML Sitemap']) || (isset($qa_modules['module']['XML Sitemap']))) ? 'You have installed a Sitemap plugin which can be replaced by "Ultimate SEO plugin". if you wish to use this plugin\'s sitemap features please remove it.' : $this->get_page_contents('sitemap.php')) . '
						</div>  
						<div class="tab_identifier_child">
							' . $this->get_page_contents('accessibility.php') . '
						</div>  
						<div class="tab_identifier_child">
							' . $this->get_page_contents('meta-tags.php') . '
						</div>
						<div class="tab_identifier_child">
							' . $this->get_page_contents('social-sharing.php') . '
						</div>  
						<div class="tab_identifier_child">
							' . $this->get_page_contents('tags.php') . '
						</div>  
						<div class="tab_identifier_child">
							' . $this->get_page_contents('categories.php') . '
						</div>  
					</div>
				</div>
				<section class="useo-buttons-container">
					<input class="qa-form-tall-button qa-form-tall-button-save useo-right" type="submit" title="" value="Save Changes" name="useo_save">
					<input class="qa-form-tall-button" type="submit" title="" value="Reset Settings" name="useo_reset">
				</section>
			</form>
		';

        return $output;
    }

    function get_page_contents($page)
    {
        ob_start();
        include $this->directory . 'option-pages/' . $page;
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }
}
