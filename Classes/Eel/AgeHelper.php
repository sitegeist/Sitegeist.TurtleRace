<?php

namespace Sitegeist\TurtleRace\Eel;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Eel\FlowQuery\FlowQuery;

class AgeHelper implements ProtectedContextAwareInterface
{
    /**
     * @param $documentNode
     * @return DateTime
     */
    public function documentAge(NodeInterface $documentNode): ?\DateTime
    {
        $childNodes = (new FlowQuery([$documentNode]))->children('[instanceof Neos.Neos:Content]')->get();
        if ($childNodes) {
            $deepChildren = (new FlowQuery($childNodes))->find('[instanceof Neos.Neos:Content]')->get();
        } else {
            $deepChildren = [];
        }
        $allPossibleNodes = array_merge($childNodes, $deepChildren);
        $age = $documentNode->getNodeData()->getLastPublicationDateTime();

        /**
         * @var NodeInterface $childNode
         */
        foreach ($allPossibleNodes as $childNode) {
            $nodeAge = $childNode->getNodeData()->getLastPublicationDateTime();
            if ($nodeAge && $nodeAge < $age ) {
                $age = $nodeAge;
            }
        }
        return $age;
    }

    public function allowsCallOfMethod($methodName)
    {
        return true;
    }


}
