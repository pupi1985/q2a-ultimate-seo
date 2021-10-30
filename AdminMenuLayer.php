<?php

class qa_html_theme_layer extends qa_html_theme_base
{

    public function initialize()
    {
        parent::initialize();

        $this->adminMenuItem();
    }

    private function adminMenuItem()
    {
        if ($this->template !== 'admin') {
            return;
        }

        // For qa_get_logged_in_level(), QA_USER_LEVEL_ADMIN
        require_once QA_INCLUDE_DIR . 'app/posts.php';

        if (qa_get_logged_in_level() < QA_USER_LEVEL_ADMIN) {
            return;
        }

        $this->content['navigation']['sub']['ultimate_seo'] = array(
            'label' => 'Ultimate SEO',
            'url' => qa_path_html('admin/ultimate_seo'),
        );

        if ($this->request === 'admin/ultimate_seo') {
            if (empty($this->content['navigation']['sub'])) {
                $this->content['navigation']['sub'] = array();
            }

            // For qa_admin_sub_navigation()
            require_once QA_INCLUDE_DIR . 'app/admin.php';

            $this->content['navigation']['sub'] = array_merge(
                qa_admin_sub_navigation(),
                $this->content['navigation']['sub']
            );
            $this->content['navigation']['sub']['ultimate_seo']['selected'] = true;
        }
    }
}
