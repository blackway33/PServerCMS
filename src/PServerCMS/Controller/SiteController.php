<?php

namespace PServerCMS\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class SiteController extends AbstractActionController
{
    /** @var \PServerCMS\Service\Download */
    protected $downloadService;
    /** @var \PServerCMS\Service\PageInfo */
    protected $pageInfoService;

    /**
     * DownloadPage
     */
    public function downloadAction()
    {
        return array(
            'downloadList' => $this->getDownloadService()->getActiveList()
        );
    }

    /**
     * DynamicPages
     */
    public function pageAction()
    {
        $type     = $this->params()->fromRoute( 'type' );
        $pageInfo = $this->getPageInfoService()->getPage4Type( $type );
        if (!$pageInfo) {
            return $this->redirect()->toRoute( 'PServerCMS' );
        }

        return array(
            'pageInfo' => $pageInfo
        );
    }

    /**
     * @return \PServerCMS\Service\Download
     */
    protected function getDownloadService()
    {
        if (!$this->downloadService) {
            $this->downloadService = $this->getServiceLocator()->get( 'pserver_download_service' );
        }

        return $this->downloadService;
    }

    /**
     * @return \PServerCMS\Service\PageInfo
     */
    protected function getPageInfoService()
    {
        if (!$this->pageInfoService) {
            $this->pageInfoService = $this->getServiceLocator()->get( 'pserver_pageinfo_service' );
        }

        return $this->pageInfoService;
    }
} 