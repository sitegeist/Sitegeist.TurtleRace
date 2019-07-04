<?php
namespace Sitegeist\TurtleRace\Domain\Repository;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Domain\Model\NodeData;
use Neos\ContentRepository\Domain\Repository\NodeDataRepository;
use Neos\Flow\Persistence\QueryInterface;
use Neos\Flow\Persistence\QueryResultInterface;

/**
 * Class NodeDataRepository
 * @package Sitegeist\TurtleRace\Domain\Repository
 * @Flow\Scope("singleton")
 */
class DocumentNodeDataRepository extends NodeDataRepository
{
    const ENTITY_CLASSNAME = NodeData::class;

    /**
     * @param NodeData $baseNodeData
     * @return QueryResultInterface
     * @throws \Neos\Flow\Persistence\Exception\InvalidQueryException
     */
    public function findLiveDocuments(NodeData $baseNodeData)
    {
        $dimensionsHash = $baseNodeData->getDimensionsHash();
        $baseNodeTypeName = 'Neos.Neos:Document';
        $documentNodeTypes = $this->nodeTypeManager->getSubNodeTypes($baseNodeTypeName,false);

        $query = $this->createQuery();
        $constraints = [
            $query->equals('workspace', 'live'),
            $query->equals('dimensionsHash', $dimensionsHash),
            $query->like('path', $baseNodeData->getPath() . '%'),
            $query->in('nodeType', array_map(function($item) {return $item->getName();}, $documentNodeTypes))
        ];

        $query->matching($query->logicalAnd($constraints));
        $query->setOrderings(['path' => QueryInterface::ORDER_ASCENDING]);
        return $query->execute();
    }
}
