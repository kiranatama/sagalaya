<?php
namespace Doctrine\ORM\Id;

use Doctrine\ORM\EntityManager;

class UUIDGenerator extends AbstractIdGenerator {
	
	public function generate(EntityManager $em, $entity) {
		return \lithium\util\String::uuid();
	}
	
	public function isPostInsertGenerator() {
		return false;
	}
} 