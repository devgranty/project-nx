<?php

/**
 * 
 * @author Grant Adiele <grantobioma@gmail.com>
 * 
 * @version 1.0.0
 * Pagination class provides easy paginate method(s)
 * which aids load multiple pages easily.
 * 
 */
namespace Classes;

class Pagination{
    
    public static function paginate(string $pageName, int $page, int $last, array $additionalQuery = []){
        $links = 2;
        $start = (($page - $links) > 0) ? $page - $links : 1;
        $end = (($page + $links) < $last) ? $page + $links : $last;
        if(is_array($additionalQuery)){
            if(!empty($additionalQuery)){
                $additionalQueries = '';
                foreach($additionalQuery as $key => $value){
                    $additionalQueries .= "$key=$value&";
                }
                $query = $additionalQueries;
            }else{
                $query = '';
            }
        }else{
            return "Array required on parameter 4.";
        }
        if($page > $last){
            return "Error: no page to display.";
        }
        $html = "<ul class='pagination justify-content-center'>";
        $addClass = ($page == 1) ? 'disabled' : '';
        if(($page-1) >= 1){
            $href = SROOT.$pageName.'?'.$query.'page='.($page-1);
            $html .= "<li class='page-item $addClass'><a href='$href' class='page-link' aria-label='previous'><span aria-hidden='true'>&laquo;</span></li>";
        }
        if($start > 1){
            $href = SROOT.$pageName.'?'.$query.'page=1';
            $html .= "<li class='page-item'><a href='$href' class='page-link'>1</a></li>";
            $html .= "<li class='page-item disabled'><span>...</span></li>";
        }
        for($i=$start; $i <= $end; $i++){
            $addClass = ($page == $i) ? 'active' : '';
            $href = SROOT.$pageName.'?'.$query.'page='.($i);
            $html .= "<li class='page-item $addClass'><a href='$href' class='page-link'>$i</a></li>";
        }
        if($end < $last){
            $html .= "<li class='page-item disabled'><span>...</span></li>";
            $href = SROOT.$pageName.'?'.$query.'page='.($last);
            $html .= "<li class='page-item'><a href='$href' class='page-link'>$last</a></li>";
        }
        $addClass = ($page == $last) ? 'disabled' : '';
        if(($page+1) <= $last){
            $href = SROOT.$pageName.'?'.$query.'page='.($page+1);
            $html .= "<li class='page-item $addClass'><a href='$href' class='page-link' aria-label='next'><span aria-hidden='true'>&raquo;</span></a></li>";
        }
        $html .= "</ul>";

        return $html;
    }
}