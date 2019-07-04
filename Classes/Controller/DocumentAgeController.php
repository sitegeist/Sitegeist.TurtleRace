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
     * @var ContextFactory
     * @Flow\Inject
     */
    protected $contextFactory;

    protected $pageLength = 20;

    /**
     * @var SiteRepository
     * @Flow\Inject()
     */
    protected $siteRepository;

    /**
     * @param int $page
     */
    public function indexAction($page = 0)
    {
        $context = $this->contextFactory->create();
        $site = $this->siteRepository->findDefault();
        $siteNode = $context->getNode('/sites/' . $site->getNodeName());
        $documemtNodeDataQuery = $this->documentNodeDataRepository->findLiveDocuments($siteNode->getNodeData());

        $query = $documemtNodeDataQuery->getQuery();
        $query->setLimit($this->pageLength);
        $query->setOffset($page * $this->pageLength);
        $limitedQuery = $query->execute();

        $count = $documemtNodeDataQuery->count();
        $documentNodes = array_map(
            function(NodeData $nodeData) use ($context) {return $context->getNodeByIdentifier($nodeData->getIdentifier());},
            $limitedQuery->toArray()
        );

        $this->view->assignMultiple([
            'documentNodes' => array_filter($documentNodes),
            'count' => $count,
            'page' => $page,
            'pages' => ($count > 0 ? ceil ($count / $this->pageLength) : 0)
        ]);
    }
}
