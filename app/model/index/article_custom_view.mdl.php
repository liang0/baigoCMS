<?php
/*-----------------------------------------------------------------
！！！！警告！！！！
以下为系统文件，请勿修改
-----------------------------------------------------------------*/

namespace app\model\index;

use ginkgo\Loader;
use ginkgo\Func;

//不能非法包含或直接执行
defined('IN_GINKGO') or exit('Access Denied');

/*-------------前台文章模型-------------*/
class Article_Custom_View extends Article {

    /**
     * mdl_list function.
     *
     * @access public
     * @param mixed $num_no
     * @param int $num_except (default: 0)
     * @param string $str_key (default: '')
     * @param string $str_year (default: '')
     * @param string $str_month (default: '')
     * @param bool $arr_cateIds (default: false)
     * @param bool $arr_markIds (default: false)
     * @param string $str_attachType (default: '')
     * @param string $str_orderType (default: '')
     * @return void
     */
    function lists($num_no, $num_except = 0, $arr_search = array(), $arr_order = array(), $arr_group = array()) {
        $_arr_articleSelect = array(
            'article_id',
            'article_title',
            'article_cate_id',
            'article_excerpt',
            'article_link',
            'article_time',
            'article_time_show',
            'article_is_time_pub',
            'article_time_pub',
            'article_is_time_hide',
            'article_time_hide',
            'article_attach_id',
            'article_is_gen',
            'article_hits_day',
            'article_hits_week',
            'article_hits_month',
            'article_hits_year',
            'article_hits_all',
        );

        $_arr_where = $this->queryProcess($arr_search);

        if (Func::isEmpty($arr_order)) {
            $arr_order = array(
                array('article_top', 'DESC'),
                array('article_time_pub', 'DESC'),
                array('article_id', 'DESC'),
            );
        }

        if (Func::isEmpty($arr_group)) {
            $arr_group = array('article_top', 'article_time_pub', 'article_id');
        }


        $_arr_articleRows = $this->where($_arr_where)->whereAnd($this->whereAnd_1)->whereAnd($this->whereAnd_2)->whereAnd($this->whereAnd_3)->order($arr_order)->group($arr_group)->limit($num_except, $num_no)->select($_arr_articleSelect);

        if (!Func::isEmpty($_arr_articleRows)) {
            foreach ($_arr_articleRows as $_key=>$_value) {
                $_arr_articleRows[$_key] = $this->rowProcess($_value);
                $_arr_articleRows[$_key]['article_customs']   = $this->mdl_articleCustom->read($_value['article_id']);
            }
        }

        return $_arr_articleRows;
    }


    /**
     * mdl_count function.
     *
     * @access public
     * @param string $str_key (default: '')
     * @param string $str_year (default: '')
     * @param string $str_month (default: '')
     * @param bool $arr_cateIds (default: false)
     * @param bool $arr_markIds (default: false)
     * @param string $str_attachType (default: '')
     * @param string $str_orderType (default: '')
     * @return void
     */
    function count($arr_search = array(), $arr_group = array()) {
        $_arr_where = $this->queryProcess($arr_search);

        if (Func::isEmpty($arr_group)) {
            $arr_group = array('article_top', 'article_time_pub', 'article_id');
        }

        $_num_articleCount    = $this->where($_arr_where)->whereAnd($this->whereAnd_1)->whereAnd($this->whereAnd_2)->whereAnd($this->whereAnd_3)->group($arr_group)->count();

        return $_num_articleCount;
    }


    protected function queryProcess($arr_search = array()) {
        $_arr_where = array(
            array('article_status', '=', 'pub'),
            array('article_box', '=', 'normal'),
        );

        if (isset($arr_search['key']) && !Func::isEmpty($arr_search['key'])) {
            $_arr_where[] = array('article_title|article_id', 'LIKE', '%' . $arr_search['key'] . '%', 'key');
        }

        if (isset($arr_search['year']) && !Func::isEmpty($arr_search['year'])) {
            $_arr_where[] = array('FROM_UNIXTIME(`article_time_pub`, \'%Y\')', '=', $arr_search['year'], 'year');
        }

        if (isset($arr_search['month']) && !Func::isEmpty($arr_search['month'])) {
            $_arr_where[] = array('FROM_UNIXTIME(`article_time_pub`, \'%m\')', '=', $arr_search['month'], 'month');
        }

        if (isset($arr_search['mark_id']) && $arr_search['mark_id'] > 0) {
            $_arr_where[] = array('article_mark_id', '=', $arr_search['mark_id']);
        }

        if (isset($arr_search['cate_ids']) && !Func::isEmpty($arr_search['cate_ids'])) {
            $arr_search['cate_ids'] = Func::arrayFilter($arr_search['cate_ids']);

            $_arr_where[] = array('belong_cate_id', 'IN', $arr_search['cate_ids'], 'cate_ids');
        }

        if (isset($arr_search['has_custom'])) {
            $_mdl_custom = Loader::model('Custom');

            $_arr_customRows = $_mdl_custom->cache(false);

            foreach ($_arr_customRows as $_key=>$_value) {
                if (!Func::isEmpty($arr_search['custom_' . $_value['custom_id']])) {
                    $_arr_where[] = array('custom_' . $_value['custom_id'], 'LIKE', '%' . $arr_search['custom_' . $_value['custom_id']] . '%');
                }
            }
        }

        if (isset($arr_search['attach_type'])) {
            switch ($arr_search['attach_type']) {
                case 'attach':
                    $_arr_where[] = array('article_attach_id', '>', 0);
                break;

                case 'none':
                    $_arr_where[] = array('article_attach_id', '<', 1);
                break;
            }
        }

        return $_arr_where;
    }
}
