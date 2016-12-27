<?php
        
/*              
    Plugin Name: Q2A Ultimate SEO
    Plugin URI: https://github.com/Towhidn/Q2A-Ultimate-SEO/
    Plugin Update Check URI:  https://github.com/Towhidn/Q2A-Ultimate-SEO/raw/master/q2a-ultimate-seo/qa-plugin.php
    Plugin Description: SEO Links for Question2Answer
    Plugin Version: 1.3
    Plugin Date: 2014-24-1
    Plugin Author: QA-Themes.com
    Plugin Author URI: http://QA-Themes.com
    Plugin License: copy lifted                           
    Plugin Minimum Question2Answer Version: 1.5
*/                      
                        
    if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
                    header('Location: ../../');
                    exit;   
    }
	define('USEO_DIR', dirname( __FILE__ ));
	define('USEO_VERSION', 1);

	require_once QA_INCLUDE_DIR.'qa-util-string.php';
	require_once USEO_DIR. '/library/functions.php';
	
    qa_register_plugin_module('page', 'options.php', 'useo_options', 'Ultimate SEO Options');
	
	qa_register_plugin_layer('layer.php', 'Ultimate SEO Layer');

	qa_register_plugin_overrides('overrides.php');

	qa_register_plugin_module('page', '/library/scalable-xml-sitemaps.php', 'useo_scalable_xml_sitemaps', 'Ultimate SEO Scalable XML Sitemaps');
	
	qa_register_plugin_module('page', 'tag-editor-page.php', 'useo_tag_editor_page', 'Ultimate SEO Tag Description Editor');
	
	qa_register_plugin_module('page', 'category-editor-page.php', 'useo_category_editor_page', 'Ultimate SEO Category Description Editor');

	qa_register_plugin_module('widget', 'tag-widget.php', 'useo_tag_widget', 'Ultimate SEO - Tag Descriptions');

	qa_register_plugin_module('widget', 'category-widget.php', 'useo_category_widget', 'Ultimate SEO - Category Descriptions');

	qa_register_plugin_phrases('languages/useo-lang-*.php', 'useo');
