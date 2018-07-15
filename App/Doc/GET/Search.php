<?php

namespace App\Doc\GET;

class Search extends \Core\Controller\Controller {

    public function index(){
        $keyword = $this->g('keyword');
        if(empty($keyword)){
            $this->jump(DOCUMENT_ROOT.'/');
        }
        $param = array('doc_title' => "%{$keyword}%", 'doc_content' => "%{$keyword}%", 'tag' => "%{$keyword}%");

        $sql = "SELECT %s
                FROM {$this->prefix}doc AS d
                LEFT JOIN {$this->prefix}doc_content AS dc ON dc.doc_id = d.doc_id
                LEFT jOIN {$this->prefix}tree AS t ON t.tree_id = d.doc_tree_id
                LEFT JOIN {$this->prefix}doc_content_tag AS dct ON dct.content_id = dc.doc_content_id
                WHERE doc_title LIKE :doc_title OR doc_content LIKE :doc_content OR dct.content_tag_name LIKE :tag
                GROUP BY d.doc_id
                ";
        $result = \Model\Content::quickListContent([
            'count' => sprintf($sql, 'dc.doc_content_id'),
            'total' => 'array',
            'normal'=> sprintf($sql, 'dc.*, d.doc_title, t.tree_parent'),
            'param' => $param
        ]);

        $list = [];

        $tree = \Core\Func\CoreFunc::$param['treeList'];
        $system = \Core\Func\CoreFunc::$param['system'];

		if ($result['list'])
		{
        foreach($result['list'] as $item){

            if($system['change_version'] == 0 && $tree[$item['tree_parent']]['tree_version']  != $item['tree_version']  ){
                continue;
            }

            $list["{$item['tree_version']}|{$item['tree_parent']}"][] = $item;
            }
        }
        krsort($list);
        $this->assign('page', $result['page']);
        $this->assign('list', $list);
        $this->assign('title', "'{$keyword}'搜索结果");
        $this->layout();

    }

}
