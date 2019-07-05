<?php
namespace Sitegeist\TurtleRace\Controller;

use Neos\Flow\Annotations as Flow;
use Neos\Fusion\View\FusionView;
use Neos\Neos\Controller\Module\AbstractModuleController;
use Neos\Neos\Domain\Repository\SiteRepository;
use Sitegeist\TurtleRace\Domain\Repository\DocumentNodeDataRepository;
use Neos\ContentRepository\Domain\Model\NodeData;
use Neos\Flow\Mvc\View\ViewInterface;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Service\ContextFactory;
use Neos\Neos\Domain\Service\ContentContextFactory;

class DocumentAgeController extends AbstractModuleController
{

    /**
     * @var string
     */
    protected $defaultViewObjectName = FusionView::class;

    /**
     * @var DocumentNodeDataRepository
     * @Flow\Inject
     */
    protected $documentNodeDataRepository;

    /**
     * @var ContentContextFactory
     * @Flow\Inject
     */
    protected $contentContextFactory;

    /**
     * @var SiteRepository
     * @Flow\Inject()
     */
    protected $siteRepository;

    /**
     * @param int $page
     */
    public function indexAction($site = null, int $page = 1, int $pageLength = 20)
    {
        if (is_null($site)) {
            $site = $this->siteRepository->findDefault();
        }

        $context = $this->contentContextFactory->create();
        $siteNode = $context->getNode('/sites/' . $site->getNodeName());
        $documemtNodeDataQuery = $this->documentNodeDataRepository->findLiveDocuments($siteNode->getNodeData());

        $query = $documemtNodeDataQuery->getQuery();
        $query->setLimit($pageLength);
        $query->setOffset(($page-1) * $pageLength);
        $limitedQuery = $query->execute();

        $count = $documemtNodeDataQuery->count();
        $documentNodes = array_map(
            function(NodeData $nodeData) use ($context) {return $context->getNodeByIdentifier($nodeData->getIdentifier());},
            $limitedQuery->toArray()
        );

        $this->view->assignMultiple([
            'site' => $siteNode,
            'documentNodes' => array_filter($documentNodes),
            'count' => $count,
            'page' => $page,
            'pageLength' => $pageLength
        ]);
    }
}
