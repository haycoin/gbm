<?php
include_once ROOT_FOLDER."inc/relation.class.php";
include_once ROOT_FOLDER."inc/entity.class.php";

function test_back_inc_relation_class_fctlst(){
	$testFileList[]	='test_back_inc_relation_class_addAsParent';
	return $testFileList;
}

function test_back_inc_relation_class_addAsParent(&$args){
	$rel = new Relation();
	$ey = new Entity();
	
	$parent = $ey->create(1, 'The parent');
	$child1 = $ey->create(1, 'Child 1');
	$child2 = $ey->create(1, 'Enfant 2');
	$child3 = $ey->create(1, 'Enfant 3');

	$rel->addAsParent($parent, $child1);
	$rel->addAsParent($parent, $child2);
	$rel->addAsParent($parent, $child3);
	
	$val['All_childs'] = $rel->getChilds($parent);
	$val['parent_of_child2'] = $rel->getParents($child2);
	
	testNotEmpty($args, $val);
}