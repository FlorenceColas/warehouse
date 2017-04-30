<?php
/**
 * Created by PhpStorm.
 * User: FlorenceColas
 * Date: 02/02/16
 * Time: 14:19
 */

namespace Warehouse\View\Helper;

use Zend\View\Helper\AbstractHelper;

class PaginationHelper extends AbstractHelper
{
    private $resultsPerPage;
    private $totalResults;
    private $results;
    private $baseUrl;
    private $paging;
    private $page;
    private $param;

    public function __invoke($pagedResults, $page, $baseUrl, $resultsPerPage=5, $param=null)
    {
        $this->resultsPerPage = $resultsPerPage;
        $this->totalResults = $pagedResults->count();
        $this->results = $pagedResults;
        $this->baseUrl = $baseUrl;
        $this->page = $page;
        $this->param = $param;

        return $this->generatePaging();
    }

    /**
     * Generate paging html
     */
    private function generatePaging()
    {
        # Get total page count
        $pages = ceil($this->totalResults / $this->resultsPerPage);

        # Don't show pagination if there's only one page
        if($pages == 1)
        {
            return;
        }

        # Show back to first page if not first page
        if($this->page != 1)
        {
            if (is_null($this->param))
                $this->paging = ' <a href=' . $this->baseUrl . 'page/1><<</a>';
            else
                $this->paging = ' <a href=' . $this->baseUrl . 'page/1?table='.$this->param.'><<</a>';
        }

        # Create a link for each page
        $pageCount = 1;
        while($pageCount <= $pages)
        {
            if($this->page == $pageCount){
                if (is_null($this->param))
                    $this->paging .= ' <a href=' . $this->baseUrl . 'page/' . $pageCount . '><b>'.$pageCount.'</b></a>';
                else
                    $this->paging .= ' <a href=' . $this->baseUrl . 'page/' . $pageCount . '?table='.$this->param.'><b>'.$pageCount.'</b></a>';
            }
            else {
                if (is_null($this->param))
                    $this->paging .= ' <a href=' . $this->baseUrl . 'page/' . $pageCount . '>' . $pageCount . '</a>';
                else
                    $this->paging .= ' <a href=' . $this->baseUrl . 'page/' . $pageCount . '?table='.$this->param.'>' . $pageCount . '</a>';
            }
            $pageCount++;
        }

        # Show go to last page option if not the last page
        if($this->page != $pages)
        {
            if (is_null($this->param))
                $this->paging .= ' <a href=' . $this->baseUrl . 'page/' . $pages . '>>></a>';
            else
                $this->paging .= ' <a href=' . $this->baseUrl . 'page/' . $pages . '?table='.$this->param.'>>></a>';
        }

        return $this->paging;
    }
}
