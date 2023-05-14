<?php

class useo_scalable_xml_sitemaps
{
    function suggest_requests()
    {
        return array(
            array(
                'title' => 'Sitemap Index',
                'request' => 'sitemap-index.xml',
                'nav' => null, // 'M'=main, 'F'=footer, 'B'=before main, 'O'=opposite main, null=none
            ),
            array(
                'title' => 'Index for all sitemaps',
                'request' => 'sitemap-all.xml',
                'nav' => null, // 'M'=main, 'F'=footer, 'B'=before main, 'O'=opposite main, null=none
            ), array(
                'title' => 'Sitemap Index only Questions',
                'request' => 'sitemap.xml',
                'nav' => null, // 'M'=main, 'F'=footer, 'B'=before main, 'O'=opposite main, null=none
            ),
        );
    }

    function match_request($request)
    {
        return substr($request, 0, 7) === 'sitemap' && qa_opt('useo_sitemap_enable');
    }

    function process_request($request)
    {
        //@ini_set('display_errors', 0); // we don't want to show PHP errors inside XML

        if ($request == 'sitemap.xml') {
            $req = '';
        } else {
            $req_str = substr($request, 8, strlen($request ?? '') - 12); // extract "X-Y-Z" from "sitemap-X-Y-Z.xml"
            $req = explode('-', $req_str);
        }

        // Index Pages
        // Indexed all XML sitemaps for question's lists
        // example: sitemap.xml
        if ($req == '') {
            $this->httpHeaderContentTypeXml();
            $this->sitemap_index_header();
            $q_sitemaps = qa_db_read_one_assoc(qa_db_query_sub(
                "SELECT count(*) as total from ^posts WHERE type='Q'"
            ));
            $count = (int)qa_opt('useo_sitemap_question_count');
            $q_sitemap_count = $count > 0 ? ceil($q_sitemaps['total'] / $count) : 0;
            for ($i = 0; $i < $q_sitemap_count; $i++) {
                $this->sitemap_index_output('sitemap-' . $i . '.xml');
            }
            $this->sitemap_index_footer();

            return;
        }
        // Indexed all important XML sitemaps
        // example: sitemap-index.xml
        if ((count($req) == 1) && ($req[0] == 'index')) {
            $this->httpHeaderContentTypeXml();
            $this->sitemap_index_header();
            $this->sitemap_index();
            $this->sitemap_index_footer();

            return;
        }
        // Indexed all XML sitemaps, including categories question page
        // example: sitemap-index.xml
        if ((count($req) == 1) && ($req[0] == 'all')) {
            $this->httpHeaderContentTypeXml();
            $this->sitemap_index_header();
            $this->sitemap_all();
            $this->sitemap_index_footer();

            return;
        }

        //	Question pages
        // numbered question sitenaps
        // example: sitemap-1.xml, sitemap-12.xml
        if (count($req) == 1 && strval((int)$req[0]) == $req[0]) {
            $hotstats = qa_db_read_one_assoc(qa_db_query_sub(
                "SELECT MIN(hotness) AS base, MAX(hotness)-MIN(hotness) AS spread FROM ^posts WHERE type='Q'"
            ));

            $count = (int)qa_opt('useo_sitemap_question_count');
            $start = (int)$req[0] * $count;

            $questions = qa_db_read_all_assoc(qa_db_query_sub(
                "SELECT postid, title, hotness FROM ^posts WHERE type='Q' ORDER BY postid LIMIT #,#",
                $start, $count
            ));

            if (empty($questions)) {
                $this->httpHeaderNotFound();

                return;
            }

            $this->httpHeaderContentTypeXml();
            $this->sitemap_header();
            foreach ($questions as $question) {
                $this->sitemap_output(qa_q_request($question['postid'], $question['title']),
                    0.1 + 0.9 * ($question['hotness'] - $hotstats['base']) / (1 + $hotstats['spread']));
            }
            $this->sitemap_footer();

            return;
        }

        //User pages
        if (($req[0] == 'users') && !QA_FINAL_EXTERNAL_USERS && qa_opt('useo_sitemap_users_enable')) {
            // user's numbered sitemaps
            // example: sitemap-users-1.xml, sitemap-users-12.xml
            if (isset($req[1]) && strval((int)$req[1]) == $req[1] && (int)qa_opt('useo_sitemap_users_count') != 0) {
                $count = qa_opt('useo_sitemap_users_count');
                $start = (int)$req[1] * $count;
                $users = qa_db_read_all_assoc(qa_db_query_sub(
                    "SELECT userid, handle FROM ^users ORDER BY userid LIMIT #,#",
                    $start, $count
                ));

                if (empty($users)) {
                    $this->httpHeaderNotFound();

                    return;
                }

                $this->httpHeaderContentTypeXml();

                $this->sitemap_header();
                foreach ($users as $user) {
                    $this->sitemap_output('user/' . $user['handle'], 0.25);
                }
                $this->sitemap_footer();

                return;
            } else {
                // All users sitemap
                // example: sitemap-users.xml
                if (!isset($req[1])) {
                    $users = qa_db_read_all_assoc(qa_db_query_sub(
                        "SELECT userid, handle FROM ^users ORDER BY userid"
                    ));

                    if (empty($users)) {
                        $this->httpHeaderNotFound();

                        return;
                    }

                    $this->httpHeaderContentTypeXml();

                    $this->sitemap_header();
                    foreach ($users as $user) {
                        $this->sitemap_output('user/' . $user['handle'], 0.25);
                    }
                    $this->sitemap_footer();

                    return;
                }
            }
        }

        //	Tag pages
        if (($req[0] == 'tags') && qa_using_tags() && qa_opt('useo_sitemap_tags_enable')) {
            // link to each tag's page sitemaps
            // example: sitemap-tags-1.xml, sitemap-tags-12.xml
            if (isset($req[1]) && ((strval((int)$req[1])) == $req[1]) && ((int)qa_opt('useo_sitemap_tags_count') != 0)) {
                $count = qa_opt('useo_sitemap_tags_count');
                $start = (int)$req[1] * $count;
                $tagwords = qa_db_read_all_assoc(qa_db_query_sub(
                    "SELECT wordid, word, tagcount FROM ^words WHERE tagcount>0 ORDER BY wordid LIMIT #,#",
                    $start, $count
                ));

                if (empty($tagwords)) {
                    $this->httpHeaderNotFound();

                    return;
                }

                $this->httpHeaderContentTypeXml();
                $this->sitemap_header();
                foreach ($tagwords as $tagword) {
                    $this->sitemap_output('tag/' . $tagword['word'], 0.5 / (1 + (1 / $tagword['tagcount']))); // priority between 0.25 and 0.5 depending on tag frequency
                }
                $this->sitemap_footer();

                return;
            } else {
                // link to all tags in sitemaps
                // example: sitemap-tags.xml
                if (!isset($req[1])) {
                    $tagwords = qa_db_read_all_assoc(qa_db_query_sub(
                        "SELECT wordid, word, tagcount FROM ^words WHERE tagcount>0 ORDER BY wordid"
                    ));

                    if (empty($tagwords)) {
                        $this->httpHeaderNotFound();

                        return;
                    }

                    $this->httpHeaderContentTypeXml();

                    if (count($tagwords)) {
                        $this->sitemap_header();
                        foreach ($tagwords as $tagword) {
                            $this->sitemap_output('tag/' . $tagword['word'], 0.5 / (1 + (1 / $tagword['tagcount']))); // priority between 0.25 and 0.5 depending on tag frequency
                        }
                        $this->sitemap_footer();

                        return;
                    }
                }
            }
        }

        //	link to all category pages
        if ($req[0] == 'category' && !isset($req[1]) && qa_using_categories() && qa_opt('useo_sitemap_categories_enable')) {
            $categories = qa_db_read_all_assoc(qa_db_query_sub(
                "SELECT categoryid, backpath FROM ^categories WHERE qcount > 0 ORDER BY categoryid"
            ));

            if (empty($categories)) {
                $this->httpHeaderNotFound();

                return;
            }

            $this->httpHeaderContentTypeXml();

            $this->sitemap_header();

            foreach ($categories as $category) {
                $this->sitemap_output('questions/' . implode('/', array_reverse(explode('/', $category['backpath']))), 0.5);
            }

            $this->sitemap_footer();

            return;
        }

        // sitemap for category questions
        // category-123.xml => All questions in category 123
        // category-123-5.xml => Questions in page 5 of the category 123
        if ($req[0] == 'category' && isset($req[1]) && qa_using_categories() && qa_opt('useo_sitemap_categoriy_q_enable')) {
            if (filter_var($req[1], FILTER_VALIDATE_INT) === false) {
                $this->httpHeaderNotFound();

                return;
            }

            $categoryId = (int)$req[1];

            $sql =
                'SELECT postid, title, hotness FROM ^posts WHERE ^posts.type = "Q" ' .
                'AND categoryid = # ' .
                'ORDER BY ^posts.created DESC';

            switch (count($req)) {
                // Look for all questions in category
                case 2:
                    $questions = qa_db_read_all_assoc(qa_db_query_sub($sql, $categoryId));

                    break;
                // Look for questions in category and page
                case 3:
                    if (filter_var($req[2], FILTER_VALIDATE_INT) === false) {
                        $this->httpHeaderNotFound();

                        return;
                    }

                    $page = (int)$req[2];

                    $sql .= ' LIMIT #, #';

                    $count = qa_opt('useo_sitemap_categoriy_q_count');
                    $start = $page * $count;

                    $questions = qa_db_read_all_assoc(qa_db_query_sub($sql, $categoryId, $start, $count));

                    break;
                default:
                    $this->httpHeaderNotFound();

                    return;
            }

            $hotstats = qa_db_read_one_assoc(qa_db_query_sub(
                'SELECT MIN(hotness) AS base, MAX(hotness)-MIN(hotness) AS spread FROM ^posts WHERE type = "Q"'
            ));

            if (empty($questions)) {
                $this->httpHeaderNotFound();

                return;
            }

            $this->httpHeaderContentTypeXml();

            $this->sitemap_header();

            foreach ($questions as $question) {
                $this->sitemap_output(qa_q_request($question['postid'], $question['title']),
                    0.1 + 0.9 * ($question['hotness'] - $hotstats['base']) / (1 + $hotstats['spread']));
            }

            $this->sitemap_footer();

            return;
        }

        //	Pages in category browser

        if (qa_using_categories() && qa_opt('useo_sitemap_categories_enable')) {
            $hasOutput = false;

            $nextcategoryid = 0;
            while (1) { // only find categories with a child
                $categories = qa_db_read_all_assoc(qa_db_query_sub(
                    "SELECT parent.categoryid, parent.backpath FROM ^categories AS parent " .
                    "JOIN ^categories AS child ON child.parentid=parent.categoryid WHERE parent.categoryid>=# GROUP BY parent.categoryid LIMIT 100",
                    $nextcategoryid
                ));

                if (empty($categories)) {
                    break;
                }

                // First time output. Note this could never be executed
                if (!$hasOutput) {
                    $this->httpHeaderContentTypeXml();
                    $this->sitemap_header();

                    $this->sitemap_output('categories', 0.5);

                    $hasOutput = true;
                }

                foreach ($categories as $category) {
                    $this->sitemap_output('categories/' . implode('/', array_reverse(explode('/', $category['backpath']))), 0.5);
                    $nextcategoryid = max($nextcategoryid, $category['categoryid'] + 1);
                }
            }

            if ($hasOutput) {
                $this->sitemap_footer();
            } else {
                $this->httpHeaderNotFound();
            }
        }
    }

    function sitemap_all()
    {
        // all indexed sitemaps
        $this->sitemap_index();

        // category question's list
        $sql =
            'SELECT `categoryid` FROM `^categories` ' .
            'WHERE `qcount` > 0 ' .
            'ORDER BY `categoryid`';
        $categoryIds = qa_db_read_all_values(qa_db_query_sub($sql));

        foreach ($categoryIds as $categoryId) {
            $sql =
                'SELECT COUNT(*) FROM `^posts` ' .
                'WHERE `type` = "Q" AND `categoryid` = #';

            $qcount = (int)qa_db_read_one_value(qa_db_query_sub($sql, $categoryId));

            $count = (int)qa_opt('useo_sitemap_categoriy_q_count');

            $cat_count = ceil($qcount / $count);
            for ($i = 0; $i < $cat_count; $i++) {
                $this->sitemap_index_output(sprintf("sitemap-category-%d-%d.xml", $categoryId, $i));
            }
        }
    }

    function sitemap_index()
    {
        // question list sitemap
        $q_sitemaps = qa_db_read_one_assoc(qa_db_query_sub(
            "SELECT count(*) as total from ^posts WHERE type='Q'"
        ));
        $count = (int)qa_opt('useo_sitemap_question_count');
        $q_sitemap_count = $count > 0 ? ceil($q_sitemaps['total'] / $count) : 0;
        for ($i = 0; $i < $q_sitemap_count; $i++) {
            $this->sitemap_index_output('sitemap-' . $i . '.xml');
        }

        // user's list sitemap
        if (qa_opt('useo_sitemap_users_enable')) {
            $u_sitemaps = qa_db_read_one_assoc(qa_db_query_sub(
                "SELECT count(*) as total from ^users"
            ));
            $count = qa_opt('useo_sitemap_users_count');
            $u_sitemap_count = ceil($u_sitemaps['total'] / $count);
            for ($i = 0; $i < $u_sitemap_count; $i++) {
                $this->sitemap_index_output('sitemap-users-' . $i . '.xml');
            }
        }
        // tag's list sitemap
        if (qa_opt('useo_sitemap_tags_enable')) {
            $t_sitemaps = qa_db_read_one_assoc(qa_db_query_sub(
                "SELECT count(*) as total from ^words WHERE tagcount>0 "
            ));
            $count = qa_opt('useo_sitemap_tags_count');
            $t_sitemap_count = ceil($t_sitemaps['total'] / $count);
            for ($i = 0; $i < $t_sitemap_count; $i++) {
                $this->sitemap_index_output('sitemap-tags-' . $i . '.xml');
            }
        }
        // categories's list sitemap
        if (qa_opt('useo_sitemap_categories_enable')) {
            $this->sitemap_index_output('sitemap-category.xml');
        }
    }

    function sitemap_header()
    {
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    }

    function sitemap_footer()
    {
        echo "</urlset>\n";
    }

    function sitemap_index_header()
    {
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    }

    function sitemap_index_footer()
    {
        echo "</sitemapindex>\n";
    }

    function sitemap_output($request, $priority)
    {
        echo "\t<url>\n" .
            "\t\t<loc>" . qa_xml(qa_path($request, null, qa_opt('site_url'))) . "</loc>\n" .
            "\t\t<priority>" . max(0, min(1.0, $priority)) . "</priority>\n" .
            "\t</url>\n";
    }

    function sitemap_index_output($request)
    {
        echo "\t<sitemap>\n" .
            "\t\t<loc>" . qa_xml(qa_path($request, null, qa_opt('site_url'))) . "</loc>\n" .
            "\t</sitemap>\n";
    }

    private function httpHeaderContentTypeXml()
    {
        header('Content-type: text/xml; charset=utf-8');
    }

    private function httpHeaderNotFound()
    {
        header('HTTP/1.0 404 Not Found');
    }
}
