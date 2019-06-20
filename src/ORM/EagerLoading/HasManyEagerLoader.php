<?php

namespace SilverStripe\ORM\EagerLoading;

use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\QueryCache\DataQueryStoreInterface;

class HasManyEagerLoader implements RelationEagerLoaderInterface
{
    public function eagerLoadRelation(DataList $list, $relation, DataQueryStoreInterface $store)
    {
        $parentClass = $list->dataClass();
        $schema = DataObject::getSchema();
        $joinField = $schema->getRemoteJoinField($parentClass, $relation, 'has_many');
        $relatedClass = $schema->hasManyComponent($parentClass, $relation);
        $relatedRecords = $list->relation($relation);

        $map = [];
        foreach ($list as $item) {
            $map[$item->ID] = [];
        }
        foreach ($relatedRecords as $item) {
            $parentID = $item->$joinField;
            $map[$parentID][] = $item;
        }

        foreach ($map as $parentID => $records) {
            $query = HasManyList::create($relatedClass, $joinField)
                ->forForeignID($parentID);
            $store->persist($query->dataQuery(), $records);
        }

        return $relatedRecords;
    }
}